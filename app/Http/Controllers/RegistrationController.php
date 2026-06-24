<?php

namespace App\Http\Controllers;

use App\Mail\JoinWhatsAppGroup;
use App\Mail\RegistrationConfirmation;
use App\Mail\UpdateRegistrationLink;
use App\Models\Child;
use App\Models\ParentModel;
use App\Models\Payment;
use App\Models\StudentNumberTracker;
use Carbon\Carbon;
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
     * Validate the incoming request for registration data.
     *
     * @return array
     */
    private function validateRegistrationData(Request $request)
    {
        Log::info('Validating registration data');

        return $request->validate([
            'parent1_first_name' => 'required|string|min:2|max:50',
            'parent1_last_name' => 'required|string|min:2|max:50',
            'parent1_email' => 'required|email|max:255',
            'parent1_phone' => 'required|string|min:10|max:10',
            'parent2_first_name' => 'nullable|string|min:2|max:50',
            'parent2_last_name' => 'nullable|string|min:2|max:50',
            'parent2_email' => 'nullable|email|max:255',
            'parent2_phone' => 'nullable|string|min:10|max:10',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|min:10|max:10',
            'relationship_to_family' => 'required|string|max:255',
            'postcode' => 'nullable|integer|min:4',
            'guidelines_accepted' => 'required|accepted',
            'children' => 'required|array|min:1',
            'children.*.id' => 'nullable|integer|exists:children,id',
            'children.*.first_name' => 'required|string|max:255',
            'children.*.last_name' => 'required|string|max:255',
            'children.*.gender' => 'required|string|in:Male,Female',
            'children.*.date_of_birth' => [
                'required',
                'date_format:Y-m-d',
                'before:'.Carbon::now()->subYears(config('custom.school.minimum_child_age'))->format('Y-m-d'),
            ],
            'children.*.residency_status' => 'required|string|in:Temporary Resident,Permanent Resident,Citizen',
            'children.*.day_school_name' => 'required|string|max:255',
            'children.*.day_school_year' => 'required|string|in:Pre School,Kindergarten,Grade 1,Grade 2,Grade 3,Grade 4,Grade 5,Grade 6,Grade 7,Grade 8,Grade 9,Grade 10,Grade 11,Grade 12',
            'children.*.allergies' => 'nullable|string',
            'children.*.special_needs' => 'nullable|string',
            'children.*.dhamma_class' => 'required|string|in:Did not attend last year,Class 1 (A),Class 1 (B),Class 2 (C),Class 3 (D),Class 4 (E)',
            'children.*.sinhala_class' => 'required|string|in:Did not attend last year,Class 1 (A),Class 1 (B),Class 2 (C),Class 3 (D),Class 4 (E)',
            'children.*.year_of_first_registration' => 'nullable|integer|min:1991|max:'.now()->year,
            'children.*.photography_allowed' => 'accepted|sometimes',
        ]);
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
    public function handleRegistration(Request $request)
    {
        $validated = $this->validateRegistrationData($request);

        $data = $request->only([
            'parent1_first_name', 'parent1_last_name', 'parent1_email', 'parent1_phone',
            'parent2_first_name', 'parent2_last_name', 'parent2_email', 'parent2_phone',
            'emergency_contact_name', 'emergency_contact_phone', 'relationship_to_family',
            'postcode', 'guidelines_accepted',
        ]);
        $data['registration_status'] = ParentModel::STATUS_PENDING;
        $parent = ParentModel::create($data);

        Log::info('New parent registered', $parent->toArray());

        foreach ($validated['children'] as $childData) {
            $childData['student_number'] = $this->assignStudentNumber();
            $childData['year_of_first_registration'] = now()->year;
            $child = new Child($childData);
            $parent->children()->save($child);

            Log::info("Child created for parent_id={$parent->id}", $child->toArray());
        }

        $price = (count($validated['children']) > 1)
            ? config('custom.pricing.multiple_children')
            : config('custom.pricing.single_child');

        $stripeSession = $this->createStripeSession($parent, $price);

        return redirect($stripeSession->url);
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

        // 3) Build the success URL with the token
        // e.g., /registration/success/{parentId}?token=<the-random-string>
        $successUrl = route('registration.success', ['parent' => $parent->id]).'?token='.$paymentToken.'&amount='.$price;

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

        // 3) If token is valid, create the Payment
        $payment = Payment::create([
            'parent_id' => $parent->id,
            'amount_paid' => $request->query('amount'),
            'paid_date' => now(),
        ]);

        // 4) Mark as completed
        $parent->update(['registration_status' => ParentModel::STATUS_COMPLETED]);
        Log::info('Payment recorded', $payment->toArray());

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
    public function handleUpdate(Request $request, $token)
    {
        try {
            $parent = ParentModel::where('update_token', $token)
                ->where('token_expires_at', '>', now())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect('registration.retrieve')->withErrors(['msg' => 'That link has expired. Make sure you have clicked on the latest email or else enter your email and try again.']);
        }

        $validated = $this->validateRegistrationData($request);

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

        Log::info('Parent updated via update form', $parent->toArray());

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
                Log::info('Child updated', $child->toArray());
            } else {
                $childData['student_number'] = $this->assignStudentNumber();
                $childData['year_of_first_registration'] = now()->year;
                $child = new Child($childData);
                $parent->children()->save($child);

                Log::info("New child added to parent_id={$parent->id}", $child->toArray());
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
            $price = (count($validated['children']) > 1)
                ? config('custom.pricing.multiple_children')
                : config('custom.pricing.single_child');

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

            Log::info('CSV Import: Parent created', $parent->toArray());

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

                    Log::info('CSV Import: Child created', $child->toArray());
                }
            }
        }

        return back()->with('status', 'CSV data has been successfully imported!');
    }
}
