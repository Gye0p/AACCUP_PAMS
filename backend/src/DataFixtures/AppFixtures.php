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

        // ── 2. Universities / Campuses (QUAMC-confirmed list, 58 programs) ──
        $collegesData = [
            ['Graduate School',                                         'GS'],      // 23 programs
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

        // ── 3. All 58 Programs (QUAMC portal, undergraduate and graduate) ───
        $programsData = [
            // Graduate School — 23 programs
            ['Doctor of Educational Management', 'DEDUM', 'GS', 'Level III', '2026-10-31'],
            ['Doctor of Education (Science Education, Technology Management)', 'DEDSciTech', 'GS', 'Level I', '2027-06-30'],
            ['Doctor of Management (Human Resource Management, Public Administration)', 'DMMgmt', 'GS', 'Level III', '2025-07-31'],
            ['Doctor of Philosophy (Educational Management)', 'PHDedMgt', 'GS', 'Level I', '2027-06-30'],
            ['Doctor of Philosophy in Mathematics', 'PHDMath', 'GS', 'Level II', '2028-08-15'],
            ['Master of Public Health', 'MPH', 'GS', 'Level II', '2026-08-31'],
            ['Master of Arts in Early Childhood Education', 'MAECE', 'GS', 'Level I', '2026-12-15'],
            ['Master in Technological Education (Majors: Automotive Technology, Electrical Technology, Electronics Technology, Mechanical Technology, Industrial Graphics Technology, Civil Technology)', 'MTE', 'GS', 'Level II', '2026-08-31'],
            ['Ph.D. in English Language Teaching', 'PHDelt', 'GS', 'PSV', null],
            ['Doctor of Education Major in Filipino', 'DEDFil', 'GS', 'PSV', null],
            ['Doctor of Education Major in Instruction', 'DEDInstr', 'GS', 'PSV', null],
            ['MS in Agriculture (Majors: Agronomy, Animal Science)', 'MSAgri', 'GS', 'Level I', '2021-12-15'],
            ['MA in Psychology', 'MAPsych', 'GS', 'Level II', '2028-10-31'],
            ['MA in Physical Education', 'MAPE', 'GS', 'Level II', '2028-12-15'],
            ['Master in Business Administration', 'MBA', 'GS', 'Level I', '2027-06-30'],
            ['Ph.D. in Mathematics', 'PHDMATH2', 'GS', 'Level II', '2028-06-15'],
            ['MS in Information Technology', 'MSIT', 'GS', 'Level II', '2027-06-15'],
            ['Master of Arts (Majors: Filipino, History, Science Teaching, Vocational Education, Special Education)', 'MA', 'GS', 'Level III', '2028-08-15'],
            ['Master of Science in Mathematics', 'MSMath', 'GS', 'Level II', '2025-09-30'],
            ['MA in Mathematics Teaching', 'MAMathTeach', 'GS', 'Level II', '2025-09-30'],
            ['Master of Arts in Education Major in English', 'MAEng', 'GS', 'Level II', null],
            ['Master in Public Management', 'MPM', 'GS', 'Level III', '2024-10-31'],
            ['Master of Public Administration', 'MPA', 'GS', 'Level III', '2026-10-31'],

            // CAFF — 2 programs
            ['Bachelor of Science in Fishery',                                'BSFish',   'CAFF',   'Level II', '2026-08-31'],
            ['Bachelor of Science in Forestry',                               'BSFor',    'CAFF',   'Level II', '2027-08-31'],

            // CAS — 10 programs
            ['Bachelor of Arts in General Curriculum', 'BAGC', 'CAS', 'Level II', '2026-08-31'],
            ['BS in Social Science', 'BSSS', 'CAS', 'Level II', '2018-12-31'],
            ['Bachelor of Science in Biology', 'BSBio', 'CAS', 'Level II', '2027-08-31'],
            ['Bachelor of Science in Chemistry', 'BSChem', 'CAS', 'Level II', '2025-12-15'],
            ['Bachelor of Science in Geology', 'BSGeo', 'CAS', 'Level II', '2025-01-31'],
            ['Bachelor of Science in Computer Science', 'BSCS', 'CAS', 'Level II', '2026-08-31'],
            ['Bachelor of Science in Information Technology', 'BSIT', 'CAS', 'Level III', '2029-03-15'],
            ['Bachelor of Arts in Mass Communication', 'BAMC', 'CAS', 'Level III', '2028-02-15'],
            ['Bachelor of Science in Mathematics', 'BSMath', 'CAS', 'Level III', '2026-10-31'],
            ['Bachelor of Science in Psychology', 'BSPsych', 'CAS', 'Level III', '2028-12-15'],

            // CBA — 3 programs
            ['Bachelor of Science in Accountancy', 'BSAcc', 'CBA', 'Level II', '2026-08-31'],
            ['Bachelor of Science in Business Administration', 'BSBA', 'CBA', 'Level III', '2029-02-28'],
            ['Bachelor of Science in Office Administration', 'BSOA', 'CBA', 'Level III', '2027-02-16'],

            // CCJE — 1 program
            ['Bachelor of Science in Criminology', 'BSCrim', 'CCJE', 'Level III', '2026-10-31'],

            // CEA — 8 programs
            ['Bachelor of Science in Architecture', 'BSArch', 'CEA', 'Level II', '2026-12-31'],
            ['Bachelor of Science in Civil Engineering', 'BSCE', 'CEA', 'Level II', '2027-10-31'],
            ['Bachelor of Science in Computer Engineering', 'BSCoE', 'CEA', 'Level II', '2026-12-31'],
            ['Bachelor of Science in Electrical Engineering', 'BSEE', 'CEA', 'Level II', '2025-11-15'],
            ['Bachelor of Science in Electronics Engineering', 'BSEcE', 'CEA', 'Level II', '2027-10-31'],
            ['Bachelor of Science in Geoditic Engineering', 'BSGE', 'CEA', 'Level II', '2026-12-31'],
            ['Bachelor of Science in Geothermal Engineering', 'BSGtE', 'CEA', 'Level I', '2026-08-31'],
            ['Bachelor of Science in Mechanical Engineering', 'BSME', 'CEA', 'Level II', '2027-10-31'],

            // CIT — 2 programs
            ['Bachelor of Science in Industrial Technology', 'BSIT2', 'CIT', 'Level III', '2026-10-31'],
            ['Bachelor of Technology', 'BT', 'CIT', 'PSV', '2019-12-31'],

            // CNPAHS — 2 programs
            ['Bachelor of Science in Nursing', 'BSN', 'CNPAHS', 'Level III', '2027-09-15'],
            ['Bachelor of Science in Pharmacy', 'BSPharma', 'CNPAHS', 'Level II', '2026-08-31'],

            // CTHM — 2 programs
            ['Bachelor of Science in Hospitality Management', 'BSHM', 'CTHM', 'Level III', '2027-02-15'],
            ['Bachelor of Science in Tourism Management', 'BSTM', 'CTHM', 'Level III', '2028-12-15'],

            // CTE — 5 programs
            ['Bachelor of Elementary Education', 'BEEd', 'CTE', 'Level III', '2029-09-30'],
            ['Bachelor of Secondary Education (English, Social Studies, PE)', 'BSEdA', 'CTE', 'Level III', '2026-01-15'],
            ['Bachelor of Secondary Education (Filipino, Mathematics, Science, Values)', 'BSEdB', 'CTE', 'Level I', '2027-12-15'],
            ['Bachelor of Technology and Livelihood Education', 'BTLE', 'CTE', 'Level I', '2027-12-15'],
            ['Bachelor of Technological Education', 'BTEd', 'CTE', 'Level III', '2026-12-31'],
        ];

        $programObjects = [];
        foreach ($programsData as [$name, $code, $collegeCode, $level, $validityEnd]) {
            $program = new Program();
            $program->setName($name);
            $program->setCode($code);
            $program->setCollege($collegeObjects[$collegeCode]);
            $program->setAccreditationLevel($level);
            if ($validityEnd) {
                $program->setValidityEnd(new \DateTimeImmutable($validityEnd));
            }
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
