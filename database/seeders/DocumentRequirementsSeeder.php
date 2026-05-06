<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentRequirementsSeeder extends Seeder
{
    public function run(): void
    {
        $requirements = [
            // ========== REGISTRATION SERVICE ==========
            // Adult requirements (above 4 yrs old)
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'PSA-issued Certificate of Live Birth and one (1) government-issued identification document which bears the full name, front-facing photograph, and signature or thumbmark', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Philippine Passport or ePassport issued by the Department of Foreign Affairs (DFA)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Unified Multi-purpose Identification (UMID) Card issued by the Government Service Insurance System (GSIS) or Social Security System (SSS)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Student\'s License Permit or Non-Professional/Professional Driver\'s License issued by the Land Transportation Office (LTO)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'PSA-issued Certificate of Live Birth/National Statistics Office (NSO)-issued Certificate of Live Birth with Birth Reference Number (BreN)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Barangay Certificate - Must contain full name, birthdate, father\'s full name, mother\'s maiden name, address, front facing photograph and applicant signature/thumbmark', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Local Civil Registry Office (LCRO)-issued Certificate of Live Birth', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'PSA-issued Report of Birth', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'PSA-issued Certificate of Foundling', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Integrated Bar of the Philippines (IBP) Identification Card', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Professional Regulatory Commission (PRC) ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Seaman\'s Book (Seafarer\'s Record Book)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Overseas Workers Welfare Administration (OWWA) ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Senior Citizen\'s ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'SSS ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Pantawid Pamilyang Pilipino Program (4Ps) ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'License to Own or Possess Firearms (LTOPF)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'NBI Clearance', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Police Clearance/ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Solo Parent\'s ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Person with Disability (PWD) ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Voter\'s ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Postal ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Taxpayer Identification Number (TIN)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'PhilHealth ID', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Special Resident Retiree\'s Visa (SRRV)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'National ID from other countries', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Residence ID from other countries', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Professional Identification Card', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Eligibility Card', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'adult', 'requirement' => 'Dependent\'s ID', 'is_active' => true],
            
            // Child requirements (1-4 years old) for Registration
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'Certificate of Live Birth issued by the PSA or Local Civil Registry Office (LCRO)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'Report of Birth issued by the PSA or Philippine Foreign Service Post (PFSP)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'Certificate of Foundling issued by the PSA', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'Certificate of Foundling or Certificate of Live Birth of Person with No Known Parent/s issued by the PSA', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'Municipal Form No. 102', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'Philippine Passport or ePassport issued by the Department of Foreign Affairs (DFA)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'PSA-issued Certificate of Live Birth/National Statistics Office (NSO)-issued Certificate of Live Birth with Birth Reference Number (BreN)', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'Local Civil Registry Office (LCRO)-issued Certificate of Live Birth', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'National ID of the Parent (either Mother or Father) or Guardian of the Child', 'is_active' => true],
            ['service' => 'reg', 'age_group' => 'child', 'requirement' => 'Parent or Guardian must accompany the child with valid ID', 'is_active' => true],
            
            // ========== UPDATING SERVICE ==========
            ['service' => 'updating', 'age_group' => 'adult', 'requirement' => 'Birth Certificate (for First/Last Name, Sex/DOB corrections)', 'is_active' => true],
            ['service' => 'updating', 'age_group' => 'adult', 'requirement' => 'Marriage Certificate (if applicable for name correction)', 'is_active' => true],
            ['service' => 'updating', 'age_group' => 'adult', 'requirement' => 'Barangay Certificate + Proof of Billing (utility bill) - for Address updating', 'is_active' => true],
            ['service' => 'updating', 'age_group' => 'adult', 'requirement' => 'PSA Birth Certificate (original) - for Sex/DOB correction', 'is_active' => true],
            
            // Child requirements for Updating
            ['service' => 'updating', 'age_group' => 'child', 'requirement' => 'Birth Certificate issued by PSA or LCRO', 'is_active' => true],
            ['service' => 'updating', 'age_group' => 'child', 'requirement' => 'Parent or Guardian valid ID', 'is_active' => true],
            ['service' => 'updating', 'age_group' => 'child', 'requirement' => 'Parent or Guardian must accompany the child', 'is_active' => true],
            
            // ========== INQUIRY SERVICE ==========
            ['service' => 'inquiry', 'age_group' => 'adult', 'requirement' => 'Valid Government-issued ID', 'is_active' => true],
            ['service' => 'inquiry', 'age_group' => 'adult', 'requirement' => 'Any previous transaction slip (if available)', 'is_active' => true],
            ['service' => 'inquiry', 'age_group' => 'adult', 'requirement' => 'For TRN Retrieval: Provide full name, date of birth, and sex for verification', 'is_active' => true],
            
            // Child requirements for Inquiry
            ['service' => 'inquiry', 'age_group' => 'child', 'requirement' => 'Birth Certificate of the child', 'is_active' => true],
            ['service' => 'inquiry', 'age_group' => 'child', 'requirement' => 'Parent or Guardian valid ID', 'is_active' => true],
            ['service' => 'inquiry', 'age_group' => 'child', 'requirement' => 'Parent or Guardian must accompany the child', 'is_active' => true],
        ];

        foreach ($requirements as $req) {
            DB::table('document_requirements')->insert([
                'service' => $req['service'],
                'age_group' => $req['age_group'],
                'requirement' => $req['requirement'],
                'is_active' => $req['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}