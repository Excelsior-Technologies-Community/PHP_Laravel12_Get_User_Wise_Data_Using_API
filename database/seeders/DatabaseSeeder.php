<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['id' => 1, 'name' => 'John Doe (Acme Corp)', 'email' => 'john@acme.com'],
            ['id' => 2, 'name' => 'Sarah Connor (Cyberdyne)', 'email' => 'sarah@cyberdyne.com'],
            ['id' => 3, 'name' => 'Alex Mercer (Gentek Labs)', 'email' => 'alex@gentek.com'],
        ];

        foreach ($customers as $cust) {
            User::firstOrCreate(['id' => $cust['id']], [
                'name' => $cust['name'],
                'email' => $cust['email'],
                'password' => bcrypt('password'),
            ]);
        }

        // Sample notes for Customer 1
        $c1Notes = [
            ['title' => 'Q3 Revenue & Growth Review', 'description' => 'Analyzed monthly churn rate and recurring software ARR. Target growth 25% for next fiscal cycle.', 'priority' => 'high', 'category' => 'Work', 'color' => '#ef4444', 'created_at' => Carbon::now()->subDays(1)],
            ['title' => 'Cloud Architecture Migration Plan', 'description' => 'Moving AWS microservices to Kubernetes clusters. Database failover test scheduled for midnight.', 'priority' => 'high', 'category' => 'Work', 'color' => '#3b82f6', 'created_at' => Carbon::now()->subDays(5)],
            ['title' => 'Team Sprint Retrospective & OKRs', 'description' => 'Reviewed velocity metrics, unresolved JIRA backlog items, and UI component library refactor.', 'priority' => 'medium', 'category' => 'Work', 'color' => '#f59e0b', 'created_at' => Carbon::now()->subDays(12)],
            ['title' => 'Product Roadmap & AI Assistant Feature', 'description' => 'Brainstormed conversational interface and predictive customer search modules.', 'priority' => 'medium', 'category' => 'Ideas', 'color' => '#8b5cf6', 'created_at' => Carbon::now()->subMonth()->subDays(2)],
            ['title' => 'Client NDA & Enterprise Contract Renewal', 'description' => 'Signed legal SLA document with European banking partners. Compliance ISO 27001 verified.', 'priority' => 'high', 'category' => 'Urgent', 'color' => '#ef4444', 'created_at' => Carbon::now()->subMonths(2)],
            ['title' => 'New Year Strategy Kickoff 2026', 'description' => 'Company-wide meeting regarding market expansion, hiring goals and product marketing.', 'priority' => 'low', 'category' => 'General', 'color' => '#10b981', 'created_at' => Carbon::now()->subMonths(4)],
            ['title' => 'Weekly Sync with DevOps Lead', 'description' => 'Reviewed CI/CD pipeline latency and Docker image caching performance.', 'priority' => 'low', 'category' => 'Work', 'color' => '#38bdf8', 'created_at' => Carbon::now()],
        ];

        foreach ($c1Notes as $n) {
            Note::create(array_merge($n, ['created_by' => 1]));
        }

        // Sample notes for Customer 2
        $c2Notes = [
            ['title' => 'Cybersecurity Threat Audit & Firewall Rules', 'description' => 'Updated Cloudflare WAF policies and completed annual penetration testing report.', 'priority' => 'high', 'category' => 'Urgent', 'color' => '#ef4444', 'created_at' => Carbon::now()->subDays(2)],
            ['title' => 'Customer Feedback Survey Analysis', 'description' => 'Net Promoter Score increased from 68 to 82 after the v2.4 mobile app redesign.', 'priority' => 'medium', 'category' => 'Ideas', 'color' => '#f59e0b', 'created_at' => Carbon::now()->subDays(10)],
            ['title' => 'Server Maintenance & Zero-Downtime Patching', 'description' => 'Upgraded Ubuntu kernel and PHP 8.4 runtime across production cluster.', 'priority' => 'low', 'category' => 'Work', 'color' => '#10b981', 'created_at' => Carbon::now()->subMonth()],
            ['title' => 'Payment Gateway Webhook Optimization', 'description' => 'Refactored Stripe and PayPal webhook listeners to use queued background jobs.', 'priority' => 'medium', 'category' => 'Work', 'color' => '#3b82f6', 'created_at' => Carbon::now()],
        ];

        foreach ($c2Notes as $n) {
            Note::create(array_merge($n, ['created_by' => 2]));
        }

        // Sample notes for Customer 3
        $c3Notes = [
            ['title' => 'Bio-Research Laboratory Dataset Ingestion', 'description' => 'Imported 500,000 genome sequencing records into distributed Elasticsearch index.', 'priority' => 'high', 'category' => 'Work', 'color' => '#8b5cf6', 'created_at' => Carbon::now()->subDays(3)],
            ['title' => 'Patent Application & Scientific Documentation', 'description' => 'Drafted abstract and schematic diagrams for automated cell culture monitoring device.', 'priority' => 'high', 'category' => 'Urgent', 'color' => '#ef4444', 'created_at' => Carbon::now()->subMonth()->subDays(5)],
        ];

        foreach ($c3Notes as $n) {
            Note::create(array_merge($n, ['created_by' => 3]));
        }
    }
}
