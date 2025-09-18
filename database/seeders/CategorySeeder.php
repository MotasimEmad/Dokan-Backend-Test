<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Technology',
            'Programming',
            'Web Development',
            'Mobile Development',
            'Data Science',
            'Machine Learning',
            'Artificial Intelligence',
            'DevOps',
            'Cloud Computing',
            'Cybersecurity',
            'Design',
            'UI/UX',
            'Business',
            'Marketing',
            'Finance',
            'Health & Fitness',
            'Travel',
            'Food & Cooking',
            'Education',
            'General',
            'News',
            'Entertainment',
            'Sports',
            'Gaming',
            'Photography',
            'Music',
            'Art & Culture',
            'Science',
            'Environment',
            'Politics'
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create(['name' => $category]);
        }
    }
}
