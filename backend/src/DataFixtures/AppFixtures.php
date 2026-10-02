<?php

namespace App\DataFixtures;

use App\Entity\AaccupArea;
use App\Entity\College;
use App\Entity\Program;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ── 1. Create 10 AACCUP Areas ───────────────────────────────────────
        $areas = [
            ['I',   'Mission, Vision, Goals and Objectives'],
            ['II',  'Faculty'],
            ['III', 'Curriculum and Instruction'],
            ['IV',  'Support to Students'],
            ['V',   'Research'],
            ['VI',  'Extension and Community Involvement'],
            ['VII', 'Library'],
            ['VIII','Physical Plant and Facilities'],
            ['IX',  'Laboratories'],
            ['X',   'Administration'],
        ];

        foreach ($areas as [$number, $name]) {
            $area = new AaccupArea();
            $area->setAreaNumber($number);
            $area->setName($name);
            $manager->persist($area);
        }

        // ── 2. Create 9 Colleges ─────────────────────────────────────────────
        $collegesData = [
            ['College of Arts and Sciences', 'CAS'],
            ['College of Nursing and Allied Health Sciences', 'CNAHS'],
            ['College of Engineering and Architecture', 'CEA'],
            ['College of Business and Management', 'CBM'],
            ['College of Education', 'CED'],
            ['College of Agriculture', 'CA'],
            ['College of Fisheries', 'CF'],
            ['College of Forestry and Environmental Science', 'CFES'],
            ['College of Industrial Technology', 'CIT'],
        ];

        $collegeObjects = [];
        foreach ($collegesData as [$name, $code]) {
            $college = new College();
            $college->setName($name);
            $college->setCode($code);
            $manager->persist($college);
            $collegeObjects[$code] = $college;
        }

        // ── 3. Create Programs (At least one per college) ───────────────────
        $programsData = [
            ['Bachelor of Science in Information Technology', 'BSIT', 'CAS', 'Level III'],
            ['Bachelor of Science in Mathematics', 'BSMath', 'CAS', 'Level II'],
            ['Bachelor of Science in Nursing', 'BSN', 'CNAHS', 'Level III'],
            ['Bachelor of Science in Pharmacy', 'BSPharma', 'CNAHS', 'Level I'],
            ['Bachelor of Science in Civil Engineering', 'BSCE', 'CEA', 'Level II'],
            ['Bachelor of Science in Architecture', 'BSArch', 'CEA', 'Level I'],
            ['Bachelor of Science in Business Administration', 'BSBA', 'CBM', 'Level III'],
            ['Bachelor of Secondary Education', 'BSEd', 'CED', 'Level IV'],
            ['Bachelor of Elementary Education', 'BEEd', 'CED', 'Level III'],
            ['Bachelor of Science in Agriculture', 'BSA', 'CA', 'Level II'],
            ['Bachelor of Science in Fisheries', 'BSF', 'CF', 'Level II'],
            ['Bachelor of Science in Forestry', 'BSFor', 'CFES', 'Level I'],
            ['Bachelor of Industrial Technology', 'BIT', 'CIT', 'Level II'],
        ];

        $programObjects = [];
        foreach ($programsData as [$name, $code, $collegeCode, $level]) {
            $program = new Program();
            $program->setName($name);
            $program->setCode($code);
            $program->setCollege($collegeObjects[$collegeCode]);
            $program->setAccreditationLevel($level);
            $manager->persist($program);
            $programObjects[$code] = $program;
        }

        // ── 4. Create Users (Executives, Admins, IAs) ───────────────────────
        $usersData = [
            // Executives & Admins
            ['admin@norsu.edu.ph',      'Admin',    'QUAMC',     'ROLE_QUAMC_ADMIN',          null, null],
            ['president@norsu.edu.ph',  'Roberto',  'Santos',    'ROLE_PRESIDENT',            null, null],
            ['director@norsu.edu.ph',   'Maria',    'Reyes',     'ROLE_CAMPUS_DIRECTOR',      null, null],
            ['vpaa@norsu.edu.ph',       'Jose',     'Dela Cruz', 'ROLE_VPAA',                 null, null],
            
            // Internal Accreditors (IAs)
            ['ia1@norsu.edu.ph',        'Ana',      'Lim',       'ROLE_INTERNAL_ACCREDITOR',  null, null],
            ['ia2@norsu.edu.ph',        'Mark',     'Bautista',  'ROLE_INTERNAL_ACCREDITOR',  null, null],
            ['ia3@norsu.edu.ph',        'Elena',    'Guzman',    'ROLE_INTERNAL_ACCREDITOR',  null, null],
        ];

        // ── 5. Create a Dean for EVERY College ────────────────────────────────
        foreach ($collegesData as [$name, $code]) {
            $usersData[] = [
                strtolower("dean.$code@norsu.edu.ph"), 
                'Dean', $code, 'ROLE_DEAN', $code, null
            ];
        }

        // ── 6. Create a Program Head for EVERY Program ────────────────────────
        foreach ($programsData as [$name, $code, $collegeCode]) {
            $usersData[] = [
                strtolower("ph.$code@norsu.edu.ph"), 
                'Head', $code, 'ROLE_PROGRAM_HEAD', $collegeCode, $code
            ];
        }

        // Persist all users
        foreach ($usersData as [$email, $first, $last, $role, $collegeCode, $programCode]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($first);
            $user->setLastName($last);
            $user->setRole($role);
            $user->setPassword($this->hasher->hashPassword($user, 'Password123!'));
            
            if ($collegeCode && isset($collegeObjects[$collegeCode])) {
                $user->setCollege($collegeObjects[$collegeCode]);
            }
            if ($programCode && isset($programObjects[$programCode])) {
                $user->setProgram($programObjects[$programCode]);
            }
            
            $manager->persist($user);
        }

        $manager->flush();
    }
}
