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

        $featureConversations = [
            [
                'application' => $applications['demonstratorScheduler'],
                'status' => ConversationStatus::Approved,
                'signed_off_at' => $now->copy()->subDays(2),
                'created_at' => $now->copy()->subDays(4),
                'updated_at' => $now->copy()->subDay(),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Morning! Could we add a bulk upload for demonstrator availability? Coordinators receive HR spreadsheets, but entering each time slot manually takes ages.'],
                    ['role' => 'assistant', 'minutes' => 5, 'content' => 'Absolutely. To confirm, the upload should create availability windows against each demonstrator and flag rows with unknown staff members. Anything else?'],
                    ['role' => 'user', 'minutes' => 12, 'content' => 'The CSV has staff IDs and day/time ranges. If rows do not map to a known demonstrator we should raise an inline validation error.'],
                    ['role' => 'assistant', 'minutes' => 18, 'content' => 'Great. We can parse the CSV, attach slots, and present a review screen before committing. Would a dry-run preview satisfy governance checks?'],
                    ['role' => 'user', 'minutes' => 24, 'content' => 'Yes, and please warn if a demonstrator already has a conflicting lab slot so coordinators can reshuffle.'],
                    ['role' => 'assistant', 'minutes' => 32, 'content' => 'Understood. I will reflect bulk creation, conflict detection, and clear messaging for unrecognised staff IDs in the PRD.'],
                ],
                'documents' => [
                    [
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
                        'timestamp' => $now->copy()->subDay(),
                    ],
                ],
            ],
            [
                'application' => $applications['riskAssessmentTracker'],
                'status' => ConversationStatus::InReview,
                'signed_off_at' => null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(1),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'We need to capture chemical storage limits in risk assessments. Lab supervisors keep adding this to the notes field.'],
                    ['role' => 'assistant', 'minutes' => 7, 'content' => 'Let us add a dedicated section with quantity limits and storage location. Should the review checklist change when these are present?'],
                    ['role' => 'user', 'minutes' => 15, 'content' => 'Yes, safety will want an automatic reminder if quantities exceed departmental thresholds.'],
                    ['role' => 'assistant', 'minutes' => 22, 'content' => 'Understood. I will draft the requirements so the safety team can confirm the extra workflow.'],
                ],
                'documents' => [],
            ],
            [
                'application' => $applications['examModeratorPortal'],
                'status' => ConversationStatus::Completed,
                'signed_off_at' => $now->copy()->subDays(6),
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(6),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'External examiners keep missing deadlines because there is no automated reminder in the portal.'],
                    ['role' => 'assistant', 'minutes' => 8, 'content' => 'A reminder service tied to each moderation task could help. Should reminders vary by qualification level?'],
                    ['role' => 'user', 'minutes' => 15, 'content' => 'Undergraduate and postgraduate should have separate timelines. Also copy the programme admin when reminders go out.'],
                    ['role' => 'assistant', 'minutes' => 23, 'content' => 'Got it. We will support configurable schedules per qualification level and include CC recipients. Any reporting needs?'],
                    ['role' => 'user', 'minutes' => 31, 'content' => 'Yes, we need a dashboard that flags overdue scripts so quality assurance can intervene.'],
                    ['role' => 'assistant', 'minutes' => 40, 'content' => 'Perfect. I will include reminder templates, escalation rules, and the reporting dashboard in the PRD.'],
                ],
                'documents' => [
                    [
                        'name' => 'Feature Request PRD - Exam Moderator Portal',
                        'content' => <<<'MARKDOWN'
# Objective

Introduce automated reminder schedules so external examiners submit moderation outcomes on time.

# Scope
- Configurable reminder timelines for undergraduate vs postgraduate awards.
- CC programme administrators on every reminder.
- Escalation digest for the quality assurance team.

# Deliverables
1. Reminder schedule management UI.
2. Notification templates with CC configuration.
3. Overdue moderation dashboard with export.
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(6),
                    ],
                ],
            ],
            [
                'application' => $applications['projectPairingStudio'],
                'status' => ConversationStatus::Approved,
                'signed_off_at' => $now->copy()->subDays(3),
                'created_at' => $now->copy()->subWeek(),
                'updated_at' => $now->copy()->subDays(2),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Supervisors want to block out weeks they are on research leave so they are not allocated students.'],
                    ['role' => 'assistant', 'minutes' => 6, 'content' => 'We could add supervisor availability windows. Should students see that information?'],
                    ['role' => 'user', 'minutes' => 14, 'content' => 'No, but administrators need a warning if a student preference conflicts with availability.'],
                    ['role' => 'assistant', 'minutes' => 22, 'content' => 'Understood. I will capture availability management and conflict alerts in the PRD. Anything else?'],
                    ['role' => 'user', 'minutes' => 30, 'content' => 'A report summarising unallocated students grouped by topic area would help.'],
                    ['role' => 'assistant', 'minutes' => 38, 'content' => 'Great. I will add the report requirement plus the conflict warnings.'],
                ],
                'documents' => [
                    [
                        'name' => 'Feature Request PRD - Project Pairing Studio',
                        'content' => <<<'MARKDOWN'
# Context

Supervisors frequently become unavailable during allocation, which causes manual reshuffling by administrators.

# Requirements
- Supervisors manage availability windows with start/end dates.
- Allocation engine surfaces conflicts when a preference intersects an unavailable window.
- Generate a report of unallocated students grouped by topic.

# Acceptance Criteria
1. Administrators can add, edit, and remove supervisor availability.
2. Students with conflicting preferences are flagged before allocations finalise.
3. Unallocated student report exports to CSV.
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(2),
                    ],
                ],
            ],
            [
                'application' => $applications['vmStats'],
                'status' => ConversationStatus::Approved,
                'signed_off_at' => $now->copy()->subDays(4),
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now->copy()->subDays(3),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Can VMStats alert us when teaching clusters run above 90% CPU for more than 15 minutes?'],
                    ['role' => 'assistant', 'minutes' => 5, 'content' => 'We can add threshold-based alerts. Should they integrate with Teams or email only?'],
                    ['role' => 'user', 'minutes' => 12, 'content' => 'Email is fine for now, but include a weekly digest so we can spot trends.'],
                    ['role' => 'assistant', 'minutes' => 20, 'content' => 'Understood. I will capture alert thresholds, sustained utilisation logic, and the digest.'],
                    ['role' => 'user', 'minutes' => 28, 'content' => 'Please also show the host names affected in the alert payload.'],
                    ['role' => 'assistant', 'minutes' => 36, 'content' => 'Noted. I will include host context and the digest summary requirements.'],
                ],
                'documents' => [
                    [
                        'name' => 'Feature Request PRD - VMStats Alerts',
                        'content' => <<<'MARKDOWN'
# Summary

Introduce sustained utilisation alerts so the infrastructure team can proactively scale lab capacity.

# Functional Requirements
- Configure CPU threshold per cluster (default 90%).
- Trigger alert when utilisation stays above threshold for 15 consecutive minutes.
- Email immediate alert with affected hosts and include event in weekly digest.

# Non-Functional
- Alert evaluation every five minutes.
- Notifications must send within two minutes of threshold breach.
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(3),
                    ],
                ],
            ],
        ];

        foreach ($featureConversations as $data) {
            $conversation = Conversation::factory()->create([
                'user_id' => $requestingUser->id,
                'application_id' => $data['application']->id,
                'status' => $data['status'],
                'signed_off_at' => $data['signed_off_at'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ]);

            $this->createMessageSequence($conversation, $data['messages']);

            foreach ($data['documents'] as $document) {
                $timestamp = $document['timestamp'] ?? $conversation->updated_at;

                Document::factory()->create([
                    'conversation_id' => $conversation->id,
                    'name' => $document['name'],
                    'content' => $document['content'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }
    }

    private function createNewApplicationConversations(User $requestingUser): void
    {
        $now = Carbon::now();

        $newApplicationConversations = [
            [
                'status' => ConversationStatus::Approved,
                'signed_off_at' => $now->copy()->subDays(1),
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subHours(12),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Careers want a hub to manage industry placements. We track interest in spreadsheets and placements fall through.'],
                    ['role' => 'assistant', 'minutes' => 6, 'content' => 'So we need a portal for students to register interest, match them with partners, and log weekly progress?'],
                    ['role' => 'user', 'minutes' => 13, 'content' => 'Exactly. Employers will submit opportunities and we must ensure compliance checks before offers are confirmed.'],
                    ['role' => 'assistant', 'minutes' => 21, 'content' => 'I will outline partner onboarding, student matching, and placement monitoring in the PRD. Any reporting requirements?'],
                    ['role' => 'user', 'minutes' => 28, 'content' => 'We need cohort stats for the Pro Vice-Chancellor and alerts when placements are at risk.'],
                    ['role' => 'assistant', 'minutes' => 36, 'content' => 'Great, I will capture dashboards, alerts, and weekly summaries as core deliverables.'],
                ],
                'documents' => [
                    [
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
                        'timestamp' => $now->copy()->subHours(11),
                    ],
                    [
                        'name' => 'Research Findings - Industry Placement Hub',
                        'content' => <<<'MARKDOWN'
# Summary

- Jisc Placement Toolkit covers compliance guidance but not workflow automation.
- No existing campus system integrates employers, students, and wellbeing reporting in one place.
- SaaS alternatives such as InPlace lack our required safeguarding checkpoints.

# Recommendation

Proceed with internal build focused on safeguarding workflows and institutional reporting needs.
MARKDOWN,
                        'timestamp' => $now->copy()->subHours(10),
                    ],
                ],
                'proposed_application' => [
                    'name' => 'Industry Placement Hub',
                    'short_description' => 'Manages employer partnerships and student placement journeys.',
                    'overview' => 'Provides a shared workspace for careers staff, employers, and students to track placement progress.',
                ],
            ],
            [
                'status' => ConversationStatus::Pending,
                'signed_off_at' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subHours(6),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Could we explore a portal for loaning AV equipment? Our media team use paper forms and items go missing.'],
                    ['role' => 'assistant', 'minutes' => 5, 'content' => 'We can capture loan requests, approval routing, and automated reminders. Do you need student authentication or staff only?'],
                    ['role' => 'user', 'minutes' => 11, 'content' => 'Both. Students borrow cameras, staff borrow microphones for events. Inventory tracking is essential.'],
                    ['role' => 'assistant', 'minutes' => 17, 'content' => 'I will draft the discovery notes and loop back once we confirm how inventory integrates with finance.'],
                ],
                'documents' => [],
                'proposed_application' => null,
            ],
            [
                'status' => ConversationStatus::Approved,
                'signed_off_at' => $now->copy()->subDays(4),
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now->copy()->subDays(3),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Our accessibility office wants a Digital Access Pass for tracking reasonable adjustments across departments.'],
                    ['role' => 'assistant', 'minutes' => 6, 'content' => 'So staff need to request adjustments, track fulfilment, and log review dates?'],
                    ['role' => 'user', 'minutes' => 14, 'content' => 'Yes, plus alerts when reviews are overdue. Students should have a summary of agreed support.'],
                    ['role' => 'assistant', 'minutes' => 22, 'content' => 'I will document staff workflows, student visibility, and the alerting requirements.'],
                    ['role' => 'user', 'minutes' => 30, 'content' => 'Include reporting for equality monitoring too.'],
                    ['role' => 'assistant', 'minutes' => 38, 'content' => 'Understood. I will add anonymised reporting and the compliance audit trail.'],
                ],
                'documents' => [
                    [
                        'name' => 'New Application PRD - Digital Access Pass',
                        'content' => <<<'MARKDOWN'
# Mission

Ensure students with declared needs receive consistent, trackable support across every department.

# Highlights
- Staff workflow to request, approve, and implement adjustments.
- Student-facing summary of active adjustments with review dates.
- Compliance dashboard with equality monitoring exports.

# Roadmap
1. Core case management.
2. Student portal view.
3. Equality monitoring analytics.
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(3),
                    ],
                    [
                        'name' => 'Research Findings - Digital Access Pass',
                        'content' => <<<'MARKDOWN'
# External Scan

- UCISA accessibility survey shows gaps in cross-department visibility.
- Commercial options reviewed (AccessAble, Symplicity) lack student self-service component.
- Internal stakeholders prioritise integration with student records.

# Outcome

Proceed with discovery focused on case management and student transparency.
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(3)->addHours(2),
                    ],
                ],
                'proposed_application' => [
                    'name' => 'Digital Access Pass',
                    'short_description' => 'Coordinates reasonable adjustments and student support plans.',
                    'overview' => 'Helps staff manage requests, fulfilments, and compliance reporting for inclusive learning.',
                ],
            ],
            [
                'status' => ConversationStatus::InReview,
                'signed_off_at' => $now->copy()->subDays(2),
                'created_at' => $now->copy()->subDays(6),
                'updated_at' => $now->copy()->subDay(),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Research partnerships need a platform to catalogue collaborative projects and track funding deadlines.'],
                    ['role' => 'assistant', 'minutes' => 7, 'content' => 'So records should store institutions, principal investigators, and milestones?'],
                    ['role' => 'user', 'minutes' => 15, 'content' => 'Yes, and export key dates to Outlook so academics stay on top of reporting.'],
                    ['role' => 'assistant', 'minutes' => 23, 'content' => 'I will outline data model requirements plus calendar integrations. Any compliance constraints?'],
                    ['role' => 'user', 'minutes' => 31, 'content' => 'We must keep contracts accessible to the legal team and restrict visibility by faculty.'],
                    ['role' => 'assistant', 'minutes' => 39, 'content' => 'Understood. I will capture permissions, contract storage, and milestone alerts in the brief.'],
                ],
                'documents' => [
                    [
                        'name' => 'New Application PRD - Research Collaboration Atlas',
                        'content' => <<<'MARKDOWN'
# Goal

Provide a living catalogue of cross-institution research collaborations with milestone and document tracking.

# Key Features
- Project records with partner institutions, investigators, and funding streams.
- Calendar integration pushing milestones to Outlook/Teams.
- Secure document repository with faculty-based access control.

# Questions
- Confirm retention policy for expired contracts.
- Explore linking to finance project codes.
MARKDOWN,
                        'timestamp' => $now->copy()->subDay(),
                    ],
                ],
                'proposed_application' => [
                    'name' => 'Research Collaboration Atlas',
                    'short_description' => 'Maps joint research projects, milestones, and contractual paperwork.',
                    'overview' => 'Gives academic partnerships a single place to manage collaborations and stay compliant with funding bodies.',
                ],
            ],
            [
                'status' => ConversationStatus::Approved,
                'signed_off_at' => $now->copy()->subDays(5),
                'created_at' => $now->copy()->subDays(9),
                'updated_at' => $now->copy()->subDays(4),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Apprenticeship leads want a system to monitor placements, assessor visits, and EPA readiness.'],
                    ['role' => 'assistant', 'minutes' => 6, 'content' => 'So we track learner progress, upcoming assessments, and employer feedback?'],
                    ['role' => 'user', 'minutes' => 14, 'content' => 'Exactly. Funding milestones must be visible and we need action lists for overdue reviews.'],
                    ['role' => 'assistant', 'minutes' => 22, 'content' => 'I will include funding milestones, assessor scheduling, and overdue alerts in the outline.'],
                    ['role' => 'user', 'minutes' => 30, 'content' => 'Please integrate with our reporting suite so the PVC Education can see completion rates.'],
                    ['role' => 'assistant', 'minutes' => 38, 'content' => 'Understood. I will document reporting requirements and dashboards.'],
                ],
                'documents' => [
                    [
                        'name' => 'New Application PRD - Apprenticeship Tracker',
                        'content' => <<<'MARKDOWN'
# Vision

Create a unified tracker for apprenticeship cohorts covering placements, assessor visits, and funding compliance.

# Deliverables
- Learner timeline with assessor visit scheduling.
- Funding milestone tracker with alerts for overdue actions.
- Executive dashboard showing completion and satisfaction metrics.

# Risks
- Data quality from external training providers.
- Integration effort with business intelligence platform.
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(4),
                    ],
                    [
                        'name' => 'Research Findings - Apprenticeship Tracker',
                        'content' => <<<'MARKDOWN'
# Discovery Notes

- Existing spreadsheets lack consistent audit trails.
- Ofsted requires evidence of timely assessor visits.
- Commercial MIS systems reviewed (OneFile, Bud) are cost prohibitive for our cohort size.

# Recommendation

Proceed with scoped MVP focusing on compliance tracking and PVC reporting.
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(4)->addHours(1),
                    ],
                ],
                'proposed_application' => [
                    'name' => 'Apprenticeship Tracker',
                    'short_description' => 'Follows apprenticeship cohorts from onboarding to endpoint assessment.',
                    'overview' => 'Keeps staff aligned on placements, assessor visits, and funding milestones.',
                ],
            ],
        ];

        foreach ($newApplicationConversations as $data) {
            $conversation = Conversation::factory()->create([
                'user_id' => $requestingUser->id,
                'application_id' => null,
                'status' => $data['status'],
                'signed_off_at' => $data['signed_off_at'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ]);

            $this->createMessageSequence($conversation, $data['messages']);

            foreach ($data['documents'] as $document) {
                $timestamp = $document['timestamp'] ?? $conversation->updated_at;

                Document::factory()->create([
                    'conversation_id' => $conversation->id,
                    'name' => $document['name'],
                    'content' => $document['content'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            if ($data['proposed_application']) {
                Application::factory()->proposed()->create([
                    'name' => $data['proposed_application']['name'],
                    'short_description' => $data['proposed_application']['short_description'],
                    'overview' => $data['proposed_application']['overview'],
                    'source_conversation_id' => $conversation->id,
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                ]);
            }
        }
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
