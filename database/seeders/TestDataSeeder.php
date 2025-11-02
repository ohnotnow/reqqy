<?php

namespace Database\Seeders;

use App\ConversationStatus;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TestDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        [$adminUser, $standardUser] = $this->createUsers();

        $applications = $this->createApplications();

        $this->createFeatureRequestConversations(
            requestingUser: $standardUser,
            applications: $applications
        );

        $this->createNewApplicationConversations(
            requestingUser: $standardUser
        );
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\User}
     */
    private function createUsers(): array
    {
        $adminUser = User::factory()->create([
            'username' => 'admin2x',
            'email' => 'admin2x@example.test',
            'password' => 'secret',
            'is_admin' => true,
            'forenames' => 'Alex',
            'surname' => 'Reeves',
        ]);

        $standardUser = User::factory()->create([
            'username' => 'user2x',
            'email' => 'user2x@example.test',
            'password' => 'secret',
            'is_admin' => false,
            'forenames' => 'Olivia',
            'surname' => 'McLean',
        ]);

        return [$adminUser, $standardUser];
    }

    /**
     * @return array<string, \App\Models\Application>
     */
    private function createApplications(): array
    {
        $now = Carbon::now();

        $examModeratorPortal = Application::factory()->internal()->create([
            'name' => 'Exam Moderator Portal',
            'short_description' => 'Coordinates moderation workflows across exam papers.',
            'overview' => 'Provides programme teams with a single view of moderation tasks, reminders, and sign-off tracking.',
            'is_automated' => true,
            'status' => 'Live',
            'url' => 'https://exam-moderator.university.test',
            'repo' => 'https://github.com/reqqy/exam-moderator-portal',
            'created_at' => $now->clone()->subWeeks(5),
            'updated_at' => $now->clone()->subWeeks(1),
        ]);

        $projectPairingStudio = Application::factory()->internal()->create([
            'name' => 'Project Pairing Studio',
            'short_description' => 'Matches students with final-year project supervisors.',
            'overview' => 'Automates preference capture, allocation rules, and reporting for departmental administrators.',
            'is_automated' => true,
            'status' => 'Live',
            'url' => 'https://projects.university.test',
            'repo' => 'https://github.com/reqqy/project-pairing-studio',
            'created_at' => $now->clone()->subWeeks(8),
            'updated_at' => $now->clone()->subWeeks(2),
        ]);

        $demonstratorScheduler = Application::factory()->internal()->create([
            'name' => 'Demonstrator Scheduler',
            'short_description' => 'Organises teaching assistant availability for lab sessions.',
            'overview' => 'Lets schools manage demonstrator timetables, availability windows, and room assignments.',
            'is_automated' => false,
            'status' => 'Live',
            'url' => 'https://demonstrators.university.test',
            'repo' => 'https://github.com/reqqy/demonstrator-scheduler',
            'created_at' => $now->clone()->subWeeks(6),
            'updated_at' => $now->clone()->subWeek(),
        ]);

        $riskAssessmentTracker = Application::factory()->internal()->create([
            'name' => 'Risk Assessment Tracker',
            'short_description' => 'Tracks lab and fieldwork risk assessments through review and approval.',
            'overview' => 'Captures hazards, mitigations, and approvals with full audit history for compliance teams.',
            'is_automated' => false,
            'status' => 'In Review',
            'url' => 'https://risk.university.test',
            'repo' => 'https://github.com/reqqy/risk-assessment-tracker',
            'created_at' => $now->clone()->subWeeks(4),
            'updated_at' => $now->clone()->subDays(10),
        ]);

        $vmStats = Application::factory()->internal()->create([
            'name' => 'VMStats',
            'short_description' => 'Shows live usage of teaching lab virtual machines.',
            'overview' => 'Lets IT quickly see which VM clusters are saturated and plan maintenance windows.',
            'is_automated' => true,
            'status' => 'Live',
            'url' => 'https://vmstats.university.test',
            'repo' => 'https://github.com/reqqy/vmstats',
            'created_at' => $now->clone()->subWeeks(10),
            'updated_at' => $now->clone()->subDays(7),
        ]);

        Application::factory()->external()->create([
            'name' => 'Linear',
            'short_description' => 'Lightweight project management for the product team.',
            'overview' => 'Reference tool for roadmap planning and issue triage.',
            'url' => 'https://linear.app',
            'created_at' => $now->clone()->subWeeks(12),
            'updated_at' => $now->clone()->subDays(20),
        ]);

        Application::factory()->external()->create([
            'name' => 'Zendesk',
            'short_description' => 'Handles student helpdesk tickets and knowledge base.',
            'overview' => 'Third-party SaaS the support team relies on for ticket triage.',
            'url' => 'https://zendesk.com',
            'created_at' => $now->clone()->subWeeks(20),
            'updated_at' => $now->clone()->subDays(15),
        ]);

        Application::factory()->external()->create([
            'name' => 'Miro',
            'short_description' => 'Digital whiteboards for design sprints.',
            'overview' => 'Used by the digital team for collaborative workshops.',
            'url' => 'https://miro.com',
            'created_at' => $now->clone()->subWeeks(30),
            'updated_at' => $now->clone()->subDays(30),
        ]);

        return [
            'examModeratorPortal' => $examModeratorPortal,
            'projectPairingStudio' => $projectPairingStudio,
            'demonstratorScheduler' => $demonstratorScheduler,
            'riskAssessmentTracker' => $riskAssessmentTracker,
            'vmStats' => $vmStats,
        ];
    }

    /**
     * @param  array<string, \App\Models\Application>  $applications
     */
    private function createFeatureRequestConversations(User $requestingUser, array $applications): void
    {
        $now = Carbon::now();

        $demonstratorConversation = Conversation::factory()->create([
            'user_id' => $requestingUser->id,
            'application_id' => $applications['demonstratorScheduler']->id,
            'status' => ConversationStatus::Approved,
            'signed_off_at' => $now->clone()->subDays(2),
            'created_at' => $now->clone()->subDays(4),
            'updated_at' => $now->clone()->subDay(),
        ]);

        $this->createMessageSequence($demonstratorConversation, [
            ['role' => 'user', 'minutes' => 0, 'content' => 'Morning! Could we add a bulk upload for demonstrator availability? Coordinators receive HR spreadsheets, but entering each time slot manually takes ages.'],
            ['role' => 'assistant', 'minutes' => 5, 'content' => 'Absolutely. To confirm, the upload should create availability windows against each demonstrator and flag rows with unknown staff members. Anything else?'],
            ['role' => 'user', 'minutes' => 12, 'content' => 'The CSV has staff IDs and day/time ranges. If rows do not map to a known demonstrator we should raise an inline validation error.'],
            ['role' => 'assistant', 'minutes' => 18, 'content' => 'Great. We can parse the CSV, attach slots, and present a review screen before committing. Would a dry-run preview satisfy governance checks?'],
            ['role' => 'user', 'minutes' => 24, 'content' => 'Yes, and please warn if a demonstrator already has a conflicting lab slot so coordinators can reshuffle.'],
            ['role' => 'assistant', 'minutes' => 32, 'content' => 'Understood. I will reflect bulk creation, conflict detection, and clear messaging for unrecognised staff IDs in the PRD.'],
        ]);

        Document::factory()->create([
            'conversation_id' => $demonstratorConversation->id,
            'name' => 'Feature Request PRD - Demonstrator Scheduler',
            'content' => <<<'MARKDOWN'
# Overview

Bulk upload will let school coordinators import demonstrator availability from the HR export in one action, preventing hours of data entry.

# Goals
- Parse CSV files with staff identifiers and time ranges.
- Highlight missing or unknown demonstrators before import.
- Provide conflict warnings when a proposed slot clashes with an existing assignment.

# Next Steps
1. Create upload UI with validation summary.
2. Implement dry-run preview before commit.
3. Update audit trail so bulk operations are attributed to the uploading coordinator.
MARKDOWN,
            'created_at' => $now->clone()->subDay(),
            'updated_at' => $now->clone()->subDay(),
        ]);

        $riskConversation = Conversation::factory()->create([
            'user_id' => $requestingUser->id,
            'application_id' => $applications['riskAssessmentTracker']->id,
            'status' => ConversationStatus::InReview,
            'signed_off_at' => null,
            'created_at' => $now->clone()->subDays(3),
            'updated_at' => $now->clone()->subDays(1),
        ]);

        $this->createMessageSequence($riskConversation, [
            ['role' => 'user', 'minutes' => 0, 'content' => 'We need to capture chemical storage limits in risk assessments. Lab supervisors keep adding this to the notes field.'],
            ['role' => 'assistant', 'minutes' => 7, 'content' => 'Let us add a dedicated section with quantity limits and storage location. Should the review checklist change when these are present?'],
            ['role' => 'user', 'minutes' => 15, 'content' => 'Yes, safety will want an automatic reminder if quantities exceed departmental thresholds.'],
            ['role' => 'assistant', 'minutes' => 22, 'content' => 'Understood. I will draft the requirements so the safety team can confirm the extra workflow.'],
        ]);
    }

    private function createNewApplicationConversations(User $requestingUser): void
    {
        $now = Carbon::now();

        $industryPlacementConversation = Conversation::factory()->create([
            'user_id' => $requestingUser->id,
            'application_id' => null,
            'status' => ConversationStatus::Approved,
            'signed_off_at' => $now->clone()->subDays(1),
            'created_at' => $now->clone()->subDays(5),
            'updated_at' => $now->clone()->subHours(12),
        ]);

        $this->createMessageSequence($industryPlacementConversation, [
            ['role' => 'user', 'minutes' => 0, 'content' => 'Careers want a hub to manage industry placements. We track interest in spreadsheets and placements fall through.'],
            ['role' => 'assistant', 'minutes' => 6, 'content' => 'So we need a portal for students to register interest, match them with partners, and log weekly progress?'],
            ['role' => 'user', 'minutes' => 13, 'content' => 'Exactly. Employers will submit opportunities and we must ensure compliance checks before offers are confirmed.'],
            ['role' => 'assistant', 'minutes' => 21, 'content' => 'I will outline partner onboarding, student matching, and placement monitoring in the PRD. Any reporting requirements?'],
            ['role' => 'user', 'minutes' => 28, 'content' => 'We need cohort stats for the Pro Vice-Chancellor and alerts when placements are at risk.'],
            ['role' => 'assistant', 'minutes' => 36, 'content' => 'Great, I will capture dashboards, alerts, and weekly summaries as core deliverables.'],
        ]);

        $industryPlacementApplication = Application::factory()->proposed()->create([
            'name' => 'Industry Placement Hub',
            'short_description' => 'Manages employer partnerships and student placement journeys.',
            'overview' => 'Provides a shared workspace for careers staff, employers, and students to track placement progress.',
            'source_conversation_id' => $industryPlacementConversation->id,
            'created_at' => $now->clone()->subDays(5),
            'updated_at' => $now->clone()->subHours(12),
        ]);

        $industryPlacementConversation->application_id = $industryPlacementApplication->id;
        $industryPlacementConversation->save();

        Document::factory()->create([
            'conversation_id' => $industryPlacementConversation->id,
            'name' => 'New Application PRD - Industry Placement Hub',
            'content' => <<<'MARKDOWN'
# Purpose

Create a shared system for careers staff, students, and employers to manage industry placement opportunities.

# Core Features
- Employer onboarding with document compliance checklist.
- Student preference capture and automatic partner matching rules.
- Placement monitoring dashboard with weekly check-ins and risk alerts.

# Milestones
1. MVP with partner onboarding and student submissions.
2. Matching engine with configurable weighting.
3. Analytics dashboards for institutional reporting.
MARKDOWN,
            'created_at' => $now->clone()->subHours(11),
            'updated_at' => $now->clone()->subHours(11),
        ]);

        Document::factory()->create([
            'conversation_id' => $industryPlacementConversation->id,
            'name' => 'Research Findings - Industry Placement Hub',
            'content' => <<<'MARKDOWN'
# Summary

- Jisc Placement Toolkit covers compliance guidance but not workflow automation.
- No existing campus system integrates employers, students, and wellbeing reporting in one place.
- SaaS alternatives such as InPlace lack our required safeguarding checkpoints.

# Recommendation

Proceed with internal build focused on safeguarding workflows and institutional reporting needs.
MARKDOWN,
            'created_at' => $now->clone()->subHours(10),
            'updated_at' => $now->clone()->subHours(10),
        ]);

        $equipmentLoanConversation = Conversation::factory()->create([
            'user_id' => $requestingUser->id,
            'application_id' => null,
            'status' => ConversationStatus::Pending,
            'signed_off_at' => null,
            'created_at' => $now->clone()->subDays(2),
            'updated_at' => $now->clone()->subHours(6),
        ]);

        $this->createMessageSequence($equipmentLoanConversation, [
            ['role' => 'user', 'minutes' => 0, 'content' => 'Could we explore a portal for loaning AV equipment? Our media team use paper forms and items go missing.'],
            ['role' => 'assistant', 'minutes' => 5, 'content' => 'We can capture loan requests, approval routing, and automated reminders. Do you need student authentication or staff only?'],
            ['role' => 'user', 'minutes' => 11, 'content' => 'Both. Students borrow cameras, staff borrow microphones for events. Inventory tracking is essential.'],
            ['role' => 'assistant', 'minutes' => 17, 'content' => 'I will draft the discovery notes and loop back once we confirm how inventory integrates with finance.'],
        ]);

        Application::factory()->proposed()->create([
            'name' => 'Equipment Loan Console',
            'short_description' => 'Self-service bookings and reminders for AV equipment loans.',
            'overview' => 'Streamlines loan approval, return tracking, and inventory visibility for the media services team.',
            'source_conversation_id' => $equipmentLoanConversation->id,
            'created_at' => $now->clone()->subDays(2),
            'updated_at' => $now->clone()->subHours(6),
        ]);
    }

    /**
     * @param  array<int, array{role: string, minutes: int, content: string}>  $messages
     */
    private function createMessageSequence(Conversation $conversation, array $messages): void
    {
        $baseTime = Carbon::parse($conversation->created_at);

        foreach ($messages as $message) {
            $timestamp = $baseTime->clone()->addMinutes($message['minutes']);

            Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $message['role'] === 'user' ? $conversation->user_id : null,
                'content' => $message['content'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }
}
