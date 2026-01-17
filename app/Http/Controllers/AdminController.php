<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use Illuminate\Support\Facades\Log;

/**
 * Class AdminController
 *
 * Manages admin-specific features like listing parent/child data.
 */
class AdminController extends Controller
{
    /**
     * Show all parents & children in a list (DataTables or plain HTML).
     *
     * @return \Illuminate\View\View
     */
    public function showParentStudentList()
    {
        $parents = ParentModel::with('children')->get();
        Log::info("Admin viewing parent & child list");
        return view('admin.parent_child_list', compact('parents'));
    }

    public function showOrientationList()
    {
        $parents = ParentModel::with('children')->get();
        Log::info("Admin viewing orientation list");
        return view('admin.orientation_list', compact('parents'));
    }    

    public function exportCsv()
    {
        $parents = ParentModel::with('children')->get();

        $columns = [
            'Parent1FirstName','Parent1LastName','Parent1Email','Parent1Phone',
            'Parent2FirstName','Parent2LastName','Parent2Email','Parent2Phone',
            'EmergencyContact','EmergencyPhone','Relationship','ChildFirstName',
            'ChildLastName','DOB','Residency','SchoolName','SchoolYear','Allergies',
            'SpecialNeeds','DhammaClass','SinhalaClass'
        ];

        $callback = function() use ($parents, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($parents as $parent) {
                // For each child in children
                foreach($parent->children as $child) {
                    $row = [
                        $parent->parent1_first_name,
                        $parent->parent1_last_name,
                        $parent->parent1_email,
                        $parent->parent1_phone,
                        $parent->parent2_first_name,
                        $parent->parent2_last_name,
                        $parent->parent2_email,
                        $parent->parent2_phone,
                        $parent->emergency_contact_name,
                        $parent->emergency_contact_phone,
                        $parent->relationship_to_family,
                        $child->first_name,
                        $child->last_name,
                        $child->date_of_birth,
                        $child->residency_status,
                        $child->day_school_name,
                        $child->day_school_year,
                        $child->allergies,
                        $child->special_needs,
                        $child->dhamma_class,
                        $child->sinhala_class
                    ];
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'parents_children.csv');
    }

}
