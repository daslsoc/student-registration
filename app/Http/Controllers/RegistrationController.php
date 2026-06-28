<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Mail\JoinWhatsAppGroup;
use App\Mail\RegistrationConfirmation;
use App\Mail\UpdateRegistrationLink;
use App\Models\Child;
use App\Models\ParentModel;
use App\Models\Payment;
use App\Models\StudentNumberTracker;
use App\Services\ClassAllocator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use League\Csv\Reader;
use League\Csv\Statement;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Handles school registration logic: new signups, updates, CSV import, Stripe payments.
 */
class RegistrationController extends Controller
{
    /**
     * Registration fee for a given number of children. This is the single
     * source of truth for pricing — used both when creating the Stripe
     * charge and when recording the payment, so the stored amount can never
     * diverge from what was actually charged.
     *
     * @return int|float
     */
    private function priceForChildCount(int $childCount)
    {
        return $childCount > 1
            ? config('custom.pricing.multiple_children')
            : config('custom.pricing.single_child');
    }

    /**
     * Retrieve the next student number from the tracker.
     *
     * @return int
     */
    private function assignStudentNumber()
    {
        $tracker = StudentNumberTracker::firstOrCreate(['id' => 1]);
        $tracker->current_number += 1;
        $tracker->save();

        Log::info("Assigned new student number = {$tracker->current_number}");

        return $tracker->current_number;
    }

    /**
     * Show the school registration form for a new parent/child record.
     *
     * @return View
     */
    public function showRegistrationForm()
    {
        return view('registration.form');
    }

    /**
     * Handle new registrations, saving the parent, children, and redirect to Stripe.
     *
     * @return RedirectResponse
     */
    public function handleRegistration(RegistrationRequest $request)
    {
        $validated = $request->validated();

        // If this email is already on file, don't create a duplicate family.
        // Send them to the "retrieve" flow, which emails a secure link to
        // update their existing registration.
        if ($this->findExistingParentByEmail($request->input('parent1_email'))) {
            return redirect()->route('registration.retrieve')
                ->withInput(['email' => $request->input('parent1_email')])
                ->with('status', 'It looks like you have already registered with this email address. '.
                    'Enter your email below and we will send you a secure link to view or update your details.');
        }

        $data = $request->only([
            'parent1_first_name', 'parent1_last_name', 'parent1_email', 'parent1_phone',
            'parent2_first_name', 'parent2_last_name', 'parent2_email', 'parent2_phone',
            'emergency_contact_name', 'emergency_contact_phone', 'relationship_to_family',
            'postcode', 'guidelines_accepted',
        ]);
        $data['registration_status'] = ParentModel::STATUS_PENDING;
        $parent = ParentModel::create($data);

        Log::info('New parent registered', ['id' => $parent->id]);

        foreach ($validated['children'] as $childData) {
            $childData['student_number'] = $this->assignStudentNumber();
            $childData['year_of_first_registration'] = now()->year;
            $child = new Child($childData);
            $parent->children()->save($child);

            Log::info("Child created for parent_id={$parent->id}", ['id' => $child->id, 'student_number' => $child->student_number]);
        }

        $price = $this->priceForChildCount(count($validated['children']));

        $stripeSession = $this->createStripeSession($parent, $price);

        return redirect($stripeSession->url);
    }

    /**
     * Find an existing parent record matching the given email in either the
     * parent1 or parent2 column. Returns null when no family is on file.
     */
    private function findExistingParentByEmail(?string $email): ?ParentModel
    {
        if (! $email) {
            return null;
        }

        return ParentModel::where('parent1_email', $email)
            ->orWhere('parent2_email', $email)
            ->first();
    }

    /**
     * Create Stripe checkout session.
     *
     * @param  int|float  $price
     * @return Session
     */
    public function createStripeSession(ParentModel $parent, $price)
    {
        // 1) Generate a random token (length is up to you)
        $paymentToken = Str::random(32);

        // 2) Store it on the parent record
        $parent->update(['payment_token' => $paymentToken]);

        // 3) Build the success URL with the single-use token. The amount is
        // intentionally NOT in the URL — handleSuccess recomputes it
        // server-side so it can't be tampered with on the way back.
        $successUrl = route('registration.success', ['parent' => $parent->id]).'?token='.$paymentToken;

        // 4) Set your Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));

        // 5) Create the session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'aud',
                    'product_data' => [
                        'name' => 'School Registration Payment',
                    ],
                    'unit_amount' => $price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => route('registration.form'),
        ]);

        Log::info("Stripe session created for parent_id={$parent->id} price={$price}");

        return $session;
    }

    /**
     * Once Stripe payment is successful, record payment, send email, show success page.
     *
     * @param  int  $parentId
     * @return \Illuminate\Contracts\View\View
     */
    public function handleSuccess(Request $request, $parentId)
    {
        $parent = ParentModel::findOrFail($parentId);

        // 1) Retrieve the token from query string
        $providedToken = $request->query('token');

        // 2) Check if it matches parent->payment_token (constant-time compare)
        if (! $providedToken || ! $parent->payment_token
            || ! hash_equals($parent->payment_token, $providedToken)) {
            // Token is invalid or empty => skip payment creation
            Log::warning('Tried to access the successful payment URL again');

            return redirect()->route('registration.form')
                ->withErrors(['msg' => 'Payment already recorded or not valid.']);
        }

        // 3) If token is valid, create the Payment. The amount is recomputed
        // server-side from the child count + pricing config — never taken from
        // the (user-controllable) ?amount= query string, so the recorded figure
        // always matches what Stripe was told to charge.
        $payment = Payment::create([
            'parent_id' => $parent->id,
            'amount_paid' => $this->priceForChildCount($parent->children()->count()),
            'paid_date' => now(),
        ]);

        // 4) Mark as completed
        $parent->update(['registration_status' => ParentModel::STATUS_COMPLETED]);
        Log::info('Payment recorded', ['id' => $payment->id, 'parent_id' => $payment->parent_id]);

        // 4b) Auto-allocate each child to a class for both subjects from their
        // day-school year. Bumps children.updated_at, which the attendance app
        // uses as the "what changed" clock. Admins can override later.
        $allocator = app(ClassAllocator::class);
        foreach ($parent->children as $child) {
            $class = $allocator->classForGrade($child->day_school_year);
            if ($class !== null) {
                $child->update([
                    'allocated_dhamma_class' => $class,
                    'allocated_sinhala_class' => $class,
                ]);
            }
        }

        // 5) Clear the token so it’s single-use
        $parent->update(['payment_token' => null]);

        // 6) Send confirmation mail, show success page, etc.
        Mail::to($parent->parent1_email)->send(new RegistrationConfirmation($parent));
        Mail::to($parent->parent1_email)->send(new JoinWhatsAppGroup($parent));

        if ($parent->parent2_email) {
            Mail::to($parent->parent2_email)->send(new RegistrationConfirmation($parent));
            Mail::to($parent->parent2_email)->send(new JoinWhatsAppGroup($parent));
        }

        return view('registration.success', ['parent' => $parent]);
    }

    /**
     * Show the form for retrieving existing registration via parent email.
     *
     * @return View
     */
    public function showRetrieveDetailsForm()
    {
        return view('registration.retrieve');
    }

    /**
     * Send an update link to either parent's email for re-registration or updates.
     *
     * @return RedirectResponse
     */
    public function sendUpdateLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $parent = ParentModel::where('parent1_email', $request->email)
                ->orWhere('parent2_email', $request->email)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['msg' => 'Your email address was not found. Please proceed to register by going to the registration page.']);
        }
        $token = Str::random(64);
        $expiration = now()->addHours(config('auth.registration_link_expiration', 4));

        $parent->update([
            'update_token' => $token,
            'token_expires_at' => $expiration,
        ]);

        $url = URL::temporarySignedRoute(
            'registration.update',
            $expiration,
            ['token' => $token]
        );

        // Do NOT log $url — it embeds the single-use update token.
        Log::info("Update link generated for parent_id={$parent->id}");

        Mail::to($parent->parent1_email)->send(new UpdateRegistrationLink($url));
        if ($parent->parent2_email) {
            Mail::to($parent->parent2_email)->send(new UpdateRegistrationLink($url));
        }

        return back()->with('status', 'A link to update your registration details has been sent to your email.');
    }

    /**
     * Show the update form once the user clicks on the unique link.
     *
     * @param  string  $token
     * @return View
     */
    public function showUpdateForm(Request $request, $token)
    {
        try {
            $parent = ParentModel::where('update_token', $token)
                ->where('token_expires_at', '>', now())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect('registration.retrieve')->withErrors(['msg' => 'That link has expired. Make sure you have clicked on the latest email or else enter your email and try again.']);
        }

        return view('registration.update', ['parent' => $parent]);
    }

    /**
     * Handle update of existing registration, possibly with new children, then pay again.
     *
     * @param  string  $token
     * @return RedirectResponse
     */
    public function handleUpdate(RegistrationRequest $request, $token)
    {
        try {
            $parent = ParentModel::where('update_token', $token)
                ->where('token_expires_at', '>', now())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect('registration.retrieve')->withErrors(['msg' => 'That link has expired. Make sure you have clicked on the latest email or else enter your email and try again.']);
        }

        $validated = $request->validated();

        $parent->update($request->only([
            'parent1_first_name',
            'parent1_last_name',
            'parent1_email',
            'parent1_phone',
            'parent2_first_name',
            'parent2_last_name',
            'parent2_email',
            'parent2_phone',
            'emergency_contact_name',
            'emergency_contact_phone',
            'relationship_to_family',
            'postcode',
            'guidelines_accepted',
        ]));

        Log::info('Parent updated via update form', ['id' => $parent->id]);

        $existingChildren = $parent->children()->get();
        $existingChildIds = $existingChildren->pluck('id')->toArray();
        $updatedChildIds = collect($validated['children'])->pluck('id')->toArray();

        // Update or create children
        foreach ($validated['children'] as $childData) {
            if (isset($childData['id']) && in_array($childData['id'], $existingChildIds)) {
                $child = Child::find($childData['id']);

                // Ensure photography_allowed is set to false if not present
                if (! isset($childData['photography_allowed'])) {
                    $childData['photography_allowed'] = '';
                }

                $child->update($childData);
                Log::info('Child updated', ['id' => $child->id]);
            } else {
                $childData['student_number'] = $this->assignStudentNumber();
                $childData['year_of_first_registration'] = now()->year;
                $child = new Child($childData);
                $parent->children()->save($child);

                Log::info("New child added to parent_id={$parent->id}", ['id' => $child->id, 'student_number' => $child->student_number]);
            }
        }

        // Delete removed children
        foreach ($existingChildren as $child) {
            if (! in_array($child->id, $updatedChildIds)) {
                $child->delete();
            }
        }

        // if already paid then redirect to success page
        if ($parent->registration_status != ParentModel::STATUS_COMPLETED) {
            $price = $this->priceForChildCount(count($validated['children']));

            $stripeSession = $this->createStripeSession($parent, $price);

            return redirect($stripeSession->url);
        } else {
            return back()->with('status', 'Details Updates');
        }
    }

    /**
     * Show CSV import form for admin.
     *
     * @return View
     */
    public function showImportCsvForm()
    {
        return view('registration.import');
    }

    /**
     * Handle CSV import of parent and up to 4 children columns.
     *
     * @return RedirectResponse
     */
    public function handleCsvImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'default_registration_year' => 'required|integer|min:1900|max:'.now()->year,
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $records = Statement::create()->process($csv);

        foreach ($records as $record) {
            $parent = ParentModel::create([
                'parent1_first_name' => $record['Parent1FirstName'],
                'parent1_last_name' => $record['Parent1LastName'],
                'parent1_email' => $record['Parent1Email'],
                'parent1_phone' => $record['Parent1Phone'],
                'parent2_first_name' => $record['Parent2FirstName'],
                'parent2_last_name' => $record['Parent2LastName'],
                'parent2_email' => $record['Parent2Email'],
                'parent2_phone' => $record['Parent2Phone'],
                'emergency_contact_name' => $record['EmergencyContactName'],
                'emergency_contact_phone' => $record['EmergencyContactPhone'],
                'relationship_to_family' => $record['RelationshipToFamily'],
                'postcode' => $record['Postcode'],
                'guidelines_accepted' => false,
            ]);

            Log::info('CSV Import: Parent created', ['id' => $parent->id]);

            for ($i = 1; $i <= 4; $i++) {
                if (! empty($record["Child{$i}FirstName"])) {
                    $childData = [
                        'first_name' => $record["Child{$i}FirstName"],
                        'last_name' => $record["Child{$i}LastName"],
                        'gender' => $record["Child{$i}Gender"],
                        'date_of_birth' => $record["Child{$i}DateOfBirth"],
                        'residency_status' => $record["Child{$i}ResidencyStatus"],
                        'day_school_name' => $record["Child{$i}DaySchoolName"],
                        'day_school_year' => $record["Child{$i}DaySchoolYear"],
                        'allergies' => $record["Child{$i}Allergies"],
                        'special_needs' => $record["Child{$i}SpecialNeeds"],
                        'dhamma_class' => $record["Child{$i}DhammaClass"],
                        'sinhala_class' => $record["Child{$i}SinhalaClass"],
                        'student_number' => $record["Child{$i}StudentNumber"],
                        'year_of_first_registration' => $request->input('default_registration_year'),
                        'photography_allowed' => filter_var($record["Child{$i}PhotographyAllowed"], FILTER_VALIDATE_BOOLEAN),
                    ];
                    $child = $parent->children()->create($childData);

                    Log::info('CSV Import: Child created', ['id' => $child->id, 'student_number' => $child->student_number]);
                }
            }
        }

        return back()->with('status', 'CSV data has been successfully imported!');
    }
}
