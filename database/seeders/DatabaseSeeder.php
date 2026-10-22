<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Material;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo seeders run only in local/testing environments.');
        }
        $ap = config('demo.admin_password');
        $sp = config('demo.student_password');
        if (strlen((string) $ap) < 12 || strlen((string) $sp) < 12) {
            throw new \RuntimeException('Set DEMO_ADMIN_PASSWORD and DEMO_STUDENT_PASSWORD to at least 12 characters in .env before seeding.');
        }
        User::firstOrCreate(['email' => config('demo.admin_email')], ['name' => config('demo.admin_name'), 'password' => $ap])->forceFill(['role' => 'admin'])->save();
        $senior = User::firstOrCreate(['email' => 'senior@campuskart.test'], ['name' => 'Aarav Shah', 'password' => $sp]);
        $junior = User::firstOrCreate(['email' => 'junior@campuskart.test'], ['name' => 'Sara Khan', 'password' => $sp]);
        $cats = [];
        foreach (['Textbooks', 'Handwritten Notes', 'Printed Notes', 'Reference Books', 'Other Educational Materials'] as $name) {
            $cats[] = Category::firstOrCreate(['name' => $name]);
        }
        $subjects = [];
        foreach (['Data Structures', 'Database Management', 'Operating Systems', 'Computer Networks', 'Web Development', 'Cloud Computing'] as $name) {
            $subjects[] = Subject::firstOrCreate(['name' => $name]);
        }
        $titles = ['Data Structures — Used Textbook', 'DBMS Revision Notes', 'Operating Systems — Printed Notes', 'Computer Networks Reference Guide', 'Web Development Practice Workbook', 'Cloud Computing — Original Notes', 'Data Structures Exam Revision', 'DBMS — Reference Book'];
        foreach ($titles as $i => $title) {
            $m = Material::firstOrCreate(['user_id' => $senior->id, 'title' => $title], ['category_id' => $cats[$i % 5]->id, 'subject_id' => $subjects[$i % 6]->id, 'description' => 'A well-kept study resource for diploma students. Useful for concept revision and semester preparation. Minor pencil markings; all pages intact. This physical resource is permitted for resale. Meet on campus to inspect and collect.', 'semester' => ($i % 6) + 1, 'price' => [100, 60, 80, 180, 90, 50, 70, 150][$i], 'condition' => 'good', 'status' => 'available']);
            if ($i === 0) {
                Enquiry::firstOrCreate(['material_id' => $m->id, 'buyer_id' => $junior->id], ['message' => 'Is this textbook still available? Can we meet at the college library?']);
            }
        }
    }
}
