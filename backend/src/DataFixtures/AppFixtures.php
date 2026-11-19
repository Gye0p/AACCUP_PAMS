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
            ['I',    'Mission, Vision, Goals and Objectives'],
            ['II',   'Faculty'],
            ['III',  'Curriculum and Instruction'],
            ['IV',   'Support to Students'],
            ['V',    'Research'],
            ['VI',   'Extension and Community Involvement'],
            ['VII',  'Library'],
            ['VIII', 'Physical Plant and Facilities'],
            ['IX',   'Laboratories'],
            ['X',    'Administration'],
            ['IV-1', 'Research'],
            ['IV-2', 'Performance of Graduates'],
            ['IV-3', 'Community Service'],
            ['IV-4', 'International Linkages and Consortia'],
            ['IV-5', 'Planning'],
        ];

        foreach ($areas as [$number, $name]) {
            $area = new AaccupArea();
            $area->setAreaNumber($number);
            $area->setName($name);
            $manager->persist($area);
        }

        // ── 2. Nine Colleges (QUAMC-confirmed list, 35 programs total) ───────
        $collegesData = [
            ['College of Agriculture, Forestry and Fishery',           'CAFF'],   //  2 programs
            ['College of Arts and Sciences',                           'CAS'],    // 10 programs
            ['College of Business Administration',                     'CBA'],    //  3 programs
            ['College of Criminal Justice Education',                  'CCJE'],   //  1 program
            ['College of Engineering and Architecture',                'CEA'],    //  8 programs
            ['College of Industrial Technology',                       'CIT'],    //  2 programs
            ['College of Nursing, Pharmacy and Allied Health Sciences','CNPAHS'], //  2 programs
            ['College of Tourism and Hospitality Management',          'CTHM'],   //  2 programs
            ['College of Teacher Education',                           'CTE'],    //  5 programs
        ];

        $collegeObjects = [];
        foreach ($collegesData as [$name, $code]) {
            $college = new College();
            $college->setName($name);
            $college->setCode($code);
            $manager->persist($college);
            $collegeObjects[$code] = $college;
        }

        // ── 3. All 35 Programs (QUAMC portal, in-scope only) ─────────────────
        $programsData = [
            // CAFF — 2 programs
            ['BS Fishery',                                                    'BSFish',   'CAFF',   'Level II'],
            ['BS Forestry',                                                   'BSFor',    'CAFF',   'Level II'],

            // CAS — 10 programs
            ['BA General Curriculum',                                         'BAGC',     'CAS',    'Level II'],
            ['BS Social Science',                                             'BSSS',     'CAS',    'Level II'],
            ['BS Biology',                                                    'BSBio',    'CAS',    'Level II'],
            ['BS Chemistry',                                                  'BSChem',   'CAS',    'Level II'],
            ['BS Geology',                                                    'BSGeo',    'CAS',    'Level II'],
            ['BS Computer Science',                                           'BSCS',     'CAS',    'Level II'],
            ['BS Information Technology',                                     'BSIT',     'CAS',    'Level III'],
            ['BA Mass Communication',                                         'BAMC',     'CAS',    'Level III'],
            ['BS Mathematics',                                                'BSMath',   'CAS',    'Level III'],
            ['BS Psychology',                                                 'BSPsych',  'CAS',    'Level III'],

            // CBA — 3 programs
            ['BS Accountancy',                                                'BSAcc',    'CBA',    'Level II'],
            ['BS Business Administration',                                    'BSBA',     'CBA',    'Level III'],
            ['BS Office Administration',                                      'BSOA',     'CBA',    'Level III'],

            // CCJE — 1 program
            ['BS Criminology',                                                'BSCrim',   'CCJE',   'Level III'],

            // CEA — 8 programs
            ['BS Architecture',                                               'BSArch',   'CEA',    'Level II'],
            ['BS Civil Engineering',                                          'BSCE',     'CEA',    'Level II'],
            ['BS Computer Engineering',                                       'BSCoE',    'CEA',    'Level II'],
            ['BS Electrical Engineering',                                     'BSEE',     'CEA',    'Level II'],
            ['BS Electronics Engineering',                                    'BSEcE',    'CEA',    'Level II'],
            ['BS Geodetic Engineering',                                       'BSGE',     'CEA',    'Level II'],
            ['BS Geothermal Engineering',                                     'BSGtE',    'CEA',    'Level I'],
            ['BS Mechanical Engineering',                                     'BSME',     'CEA',    'Level II'],

            // CIT — 2 programs (BTEd is in CTE)
            ['BS Industrial Technology',                                      'BSIT2',    'CIT',    'Level III'],
            ['Bachelor of Technology',                                        'BT',       'CIT',    'PSV'],

            // CNPAHS — 2 programs
            ['BS Nursing',                                                    'BSN',      'CNPAHS', 'Level III'],
            ['BS Pharmacy',                                                   'BSPharma', 'CNPAHS', 'Level II'],

            // CTHM — 2 programs
            ['BS Hospitality Management',                                     'BSHM',     'CTHM',   'Level III'],
            ['BS Tourism Management',                                         'BSTM',     'CTHM',   'Level III'],

            // CTE — 5 programs (BTEd confirmed here, not CIT)
            ['Bachelor of Elementary Education',                              'BEEd',     'CTE',    'Level III'],
            ['Bachelor of Secondary Education (English, Social Studies, PE)', 'BSEdA',    'CTE',    'Level III'],
            ['Bachelor of Secondary Education (Filipino, Math, Science, Values)', 'BSEdB','CTE',    'Level I'],
            ['Bachelor of Technology and Livelihood Education',               'BTLE',     'CTE',    'Level I'],
            ['Bachelor of Technological Education',                           'BTEd',     'CTE',    'Level III'],
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

        // ── 4. Users ─────────────────────────────────────────────────────────
        $usersData = [
            // Executives & Admins
            ['admin@norsu.edu.ph',     'Admin',   'QUAMC',     'ROLE_QUAMC_ADMIN',         null, null],
            ['president@norsu.edu.ph', 'Roberto', 'Santos',    'ROLE_PRESIDENT',           null, null],
            ['director@norsu.edu.ph',  'Maria',   'Reyes',     'ROLE_CAMPUS_DIRECTOR',     null, null],
            ['vpaa@norsu.edu.ph',      'Jose',    'Dela Cruz', 'ROLE_VPAA',                null, null],

            // Internal Accreditors
            ['ia1@norsu.edu.ph', 'Ana',   'Lim',      'ROLE_INTERNAL_ACCREDITOR', null, null],
            ['ia2@norsu.edu.ph', 'Mark',  'Bautista', 'ROLE_INTERNAL_ACCREDITOR', null, null],
            ['ia3@norsu.edu.ph', 'Elena', 'Guzman',   'ROLE_INTERNAL_ACCREDITOR', null, null],
        ];

        // Dean for every college
        foreach ($collegesData as [$name, $code]) {
            $usersData[] = [
                strtolower("dean.$code@norsu.edu.ph"),
                'Dean', $code, 'ROLE_DEAN', $code, null,
            ];
        }

        // Program Head for every program
        foreach ($programsData as [$name, $code, $collegeCode]) {
            $usersData[] = [
                strtolower("ph.$code@norsu.edu.ph"),
                'Head', $code, 'ROLE_PROGRAM_HEAD', $collegeCode, $code,
            ];
        }

        // Area Coordinator for every college (one sample per college)
        foreach ($collegesData as [$name, $code]) {
            $usersData[] = [
                strtolower("ac.$code@norsu.edu.ph"),
                'AC', $code, 'ROLE_AREA_COORDINATOR', $code, null,
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
