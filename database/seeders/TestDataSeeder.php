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
            [
                'title' => 'Final Year Project Management System (with Research)',
                'status' => ConversationStatus::Approved,
                'signed_off_at' => $now->copy()->subDays(3),
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now->copy()->subDays(3),
                'messages' => [
                    ['role' => 'user', 'minutes' => 0, 'content' => 'Our Computer Science department is drowning in spreadsheets for final year projects. Students submit proposals via email, supervisors lose track, and we have no visibility on progress.'],
                    ['role' => 'assistant', 'minutes' => 6, 'content' => 'Let us capture the full workflow. Academics propose projects, students browse and submit proposals, then you need approval and assignment workflows?'],
                    ['role' => 'user', 'minutes' => 14, 'content' => 'Exactly. Academics post project ideas, students can either pick one or propose their own. We review proposals, assign supervisors, then track milestones through to submission.'],
                    ['role' => 'assistant', 'minutes' => 22, 'content' => 'That covers the core loop. What about external projects? I imagine industry partnerships bring extra complexity.'],
                    ['role' => 'user', 'minutes' => 30, 'content' => 'Yes! Industry projects require NDA tracking and insurance verification before students can start. We currently manage this in separate folders and it is chaotic.'],
                    ['role' => 'assistant', 'minutes' => 38, 'content' => 'I will note compliance document management for external projects. How do you currently match students to projects?'],
                    ['role' => 'user', 'minutes' => 46, 'content' => 'Mostly by interest, but for oversubscribed projects we would love automated allocation based on GPA. Right now it is manual negotiation.'],
                    ['role' => 'assistant', 'minutes' => 54, 'content' => 'Automated GPA-based allocation as an option makes sense. What about assessment? How are final projects marked?'],
                    ['role' => 'user', 'minutes' => 62, 'content' => 'We use blind double marking. Two academics mark independently without seeing each other\'s scores, then we reconcile any discrepancies. The current system involves printing, anonymising, and manual spreadsheets.'],
                    ['role' => 'assistant', 'minutes' => 70, 'content' => 'Understood. So we need anonymous submission handling, independent marking workflows, and a reconciliation process. Let me summarise: project proposal workflows for staff and students, industry project compliance tracking, optional GPA-based allocation, progress monitoring, and blind double marking with reconciliation. Does that capture everything?'],
                    ['role' => 'user', 'minutes' => 78, 'content' => 'Perfect. That covers all our pain points. This would transform how we manage dissertations.'],
                    ['role' => 'assistant', 'minutes' => 86, 'content' => 'Excellent. I will draft a comprehensive PRD and investigate existing solutions that might address these requirements.'],
                ],
                'documents' => [
                    [
                        'name' => 'New Application PRD - Final Year Project Portal',
                        'content' => <<<'MARKDOWN'
# Executive Summary

The Final Year Project Portal will modernise dissertation management across the Computer Science department by replacing fragmented spreadsheets and email workflows with an integrated system covering project proposals, student allocation, progress tracking, compliance management, and assessment.

# Goals & Objectives

- Provide a centralised platform for academics to propose and manage final year projects
- Enable students to browse opportunities, submit proposals, and track their progress
- Automate compliance tracking for industry-partnered projects (NDAs, insurance)
- Support flexible allocation strategies including interest-based and GPA-weighted assignment
- Implement blind double marking workflows with built-in reconciliation tools
- Reduce administrative overhead and improve visibility for programme directors

# User Personas

## Academic Staff
Propose projects, review student proposals, provide supervision and feedback, participate in marking and moderation.

## Students (Final Year / Masters)
Browse project opportunities, submit proposals (or propose own projects), track milestones, submit deliverables, receive feedback.

## Programme Administrators
Oversee allocation processes, monitor progress across cohorts, manage compliance documentation, coordinate marking and reconciliation.

## External Partners (Industry)
Submit project briefs, review student applications, provide placement supervision (optional, Phase 2).

# Functional Requirements

## Project Proposal Management
- Academics create project listings with title, description, prerequisites, and availability
- Support for both staff-proposed and student-initiated projects
- Categorisation by research area, difficulty level, and type (individual/group)
- Draft/publish workflow with approval gates for quality assurance

## Student Proposal & Allocation
- Students browse available projects with filtering and search
- Submit expressions of interest or full proposals
- Academics review applications and provide feedback
- Manual assignment by coordinator or automated GPA-based allocation
- Waiting list management for oversubscribed projects

## Industry Project Compliance
- Dedicated workflow for external/industry projects
- Document repository for NDAs, insurance certificates, and partner agreements
- Approval checkpoints before student assignment
- Automated reminders for document expiry and renewal

## Progress Tracking & Feedback
- Milestone management with configurable stages (proposal, ethics approval, interim report, final submission)
- Students upload deliverables and progress updates
- Supervisors provide structured feedback and track engagement
- Automated alerts for overdue milestones or at-risk projects

## Blind Double Marking
- Anonymous submission handling (system strips identifiable information)
- Parallel marking workflows assigned to two independent markers
- Rubric-based or free-form marking with grade entry
- Automated reconciliation process highlighting discrepancies beyond threshold
- Moderation workflow for disputed marks with audit trail

# Non-Functional Requirements

- Integration with student records system (SITS/student ID lookup)
- Role-based access control (students, staff, coordinators, external partners)
- Accessibility compliance (WCAG 2.1 AA)
- Mobile-responsive design for student and staff access
- Audit logging for compliance and quality assurance
- Data retention policies aligned with institutional requirements

# User Stories

- As an academic, I want to propose multiple projects so students have diverse options
- As a student, I want to browse projects by research area so I can find topics matching my interests
- As a coordinator, I want to auto-allocate students by GPA when projects are oversubscribed
- As a supervisor, I want to track student progress with milestone dashboards
- As an administrator, I want to ensure industry projects have valid NDAs before assignment
- As a marker, I want to grade submissions anonymously without seeing peer marks
- As a programme director, I want to monitor completion rates and identify at-risk projects

# Technical Considerations

- Consider integration with institutional identity management (SSO via SAML/OAuth)
- Document storage strategy (local filesystem vs cloud storage for submissions)
- Notification system (email + optional in-app notifications)
- Reporting engine for cohort analytics and quality assurance metrics
- Backup and disaster recovery procedures for assessment data

# Out of Scope (MVP)

- Plagiarism detection integration (use existing institutional tools)
- Video conferencing / virtual supervision tools
- External partner portal (Phase 2 enhancement)
- Integration with ethics approval systems
- Automated project recommendation engine based on student transcripts

# Open Questions

- Should peer feedback/review be supported for group projects?
- What is the threshold percentage for mark reconciliation triggers?
- Do we need version control for project proposals (staff editing after publication)?
- Should students see supervisor availability/capacity when browsing projects?
- What level of granularity is needed for progress milestone configuration?
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(3)->addHours(2),
                    ],
                    [
                        'name' => 'Research Findings - Final Year Project Portal',
                        'content' => <<<'MARKDOWN'
# Solutions and Options

## OpenEduCat (Odoo Education – Odoo Community Association) – *Open Source*
**Description:** OpenEduCat is an open-source, Odoo-based campus management system designed specifically for higher education ([www.spacebasic.com](https://www.spacebasic.com/blogs/open-source-campus-management-system#:~:text=4)). It includes a dedicated **Thesis/Project Management** module that lets students submit project proposals online and tracks the entire workflow. It provides proposal submission with status updates ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)), supervisor/project advisor assignment, document management (drafts, feedback, final submissions) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)), online review/feedback tools ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)), and progress dashboards with automated reminders ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)). In essence, it supports academics proposing projects, receiving student proposals, approving/rejecting proposals, assigning students, and giving feedback via the system.

**Cost/Licensing:** OpenEduCat Community Edition is free and open-source (LGPL-3) ([doc.openeducat.org](https://doc.openeducat.org/legal/legal.html#:~:text=OpenEduCat%20Community%20Edition%20is%20licensed,License%20and%20the%20compatibility%20matrix)). The community version has no licensing fee; an optional Enterprise version is available for paid support and premium features.

**Key Features (vs. requirements):**
- ✔ **Project proposal workflow:** Students can submit proposals electronically; academics review, comment, and approve/reject within the system ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)).
- ✔ **Supervisor/Advisor assignment:** Academics can assign supervisors or mentors based on student interests ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)). (Fulfills "assign projects to students" if supervisors ≈ projects.)
- ✔ **Progress tracking:** Built-in dashboards show milestone progress, deadlines, and reminders ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)).
- ✔ **Feedback:** Supervisors and reviewers can annotate submissions and provide feedback online ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)).

- ❌ **Missing aspects:** No built-in module for tracking NDAs or insurance for industry projects. Automated student–project allocation (e.g. by GPA) isn't provided out-of-the-box. Double-blind marking and mark reconciliation must be handled separately (it offers review tools but no formal blind‐marking workflow).

**Higher Ed Fit:** Because it is tailored for universities, OpenEduCat aligns well with academic workflows ([www.spacebasic.com](https://www.spacebasic.com/blogs/open-source-campus-management-system#:~:text=4)). It covers most of the standard final-year project lifecycle. Its modular design (Odoo-based) means campuses can add or customize features (e.g. grading rules, compliance) ([www.spacebasic.com](https://www.spacebasic.com/blogs/open-source-campus-management-system#:~:text=,and%20HR%20tools%20for%20staff)). However, being a broad ERP/LMS, it may require development or custom modules to handle very specific needs (NDA tracking, auto-allocation algorithms, or exam-style blind double marking). Overall, OpenEduCat would likely meet a large portion (~80%) of the requirements, making it a strong candidate as a starting point.

## OpenProject (OpenProject GmbH) – *Open Source (Cloud/Self-hosted)*
**Description:** OpenProject is an open-source web-based project management tool (GPLv3) for cloud or on-premise use ([en.wikipedia.org](https://en.wikipedia.org/wiki/OpenProject#:~:text=OpenProject%20is%20project%20management%20software,free%2C%20community%20edition%20and%20a)). It supports creating and tracking projects with features like Gantt charts, task management, Kanban boards, calendars, and meeting agendas ([en.wikipedia.org](https://en.wikipedia.org/wiki/OpenProject#:~:text=Users%20can%20create%20and%20track,a%20configurable%20and%20flexible%20system)). OpenProject emphasizes transparency and collaboration; users can form projects with timelines and tasks, and team members can track progress and communicate within these projects. It is widely used in business and education settings; for example, one survey ranks OpenProject among the top project-management solutions at universities ([www.openproject.org](https://www.openproject.org/blog/project-management-software-universities/#:~:text=OpenProject%20is%20ranked%20here%20among,and%20commercial%20solutions%20in%20blue)).

**Cost/Licensing:** The Community Edition is free (open-source under GPLv3 ([en.wikipedia.org](https://en.wikipedia.org/wiki/OpenProject#:~:text=OpenProject%20is%20project%20management%20software,free%2C%20community%20edition%20and%20a))). Paid hosting or Enterprise support plans are available from the vendor.

**Key Features (vs. requirements):**
- ✔ **Project/task tracking:** Academics could set up each project as an OpenProject "project" or task list. Students and staff can view tasks, milestones, and progress. This supports general progress tracking, milestone scheduling, and collaboration.
- ✔ **Collaboration/Feedback:** Team members can comment on tasks and documents, facilitating feedback and communication.
- ❌ **Project proposals:** There's no formal proposal submission workflow. Academics could post project topics as tasks, but there is no built-in accept/reject or review process for proposals.
- ❌ **Automated assignment:** No built-in algorithm for student allocation (all assignment of tasks to users is manual).
- ❌ **NDA compliance:** No module for NDAs or external project compliance documentation.
- ❌ **Blind double marking:** OpenProject has no exam/marking features; it does not support anonymous or double blind grading.

**Higher Ed Fit:** OpenProject is a general-purpose PM tool. Its flexibility means universities use it for research project tracking or administrative planning (it is "top 5" in university project management tools ([www.openproject.org](https://www.openproject.org/blog/project-management-software-universities/#:~:text=OpenProject%20is%20ranked%20here%20among,and%20commercial%20solutions%20in%20blue))). Advantages: highly configurable, strong for timeline/task management, and open-source (so code can be extended). Limits: It is not tailored to student projects; implementing proposal submission or marking workflows would require major customization. It could handle the "project tracking" portion well, but falls short on academic-specific features.

## ERPNext (Frappe/ERPNext) – *Open Source*
**Description:** ERPNext is an open-source ERP system with an Education module. The education app manages student admissions, records, fees, course assignments, and learning outcomes ([frappe.io](https://frappe.io/erpnext/for-education#:~:text=A%20comprehensive%20solution%20to%20manage,on%20what%20matters%20most%3A%20teaching)). It provides integrated portals for students, faculty, and parents. Although primarily an ERP (finance/HR), ERPNext includes tools like assignment records and grade entry.

**Cost/Licensing:** ERPNext is free and open-source (GNU GPL), with optional paid cloud hosting or enterprise services.

**Key Features:**
- ✔ **Student records & administration:** Handles admissions, enrollment, scheduling, attendance, grading, and fee management ([frappe.io](https://frappe.io/erpnext/for-education#:~:text=A%20comprehensive%20solution%20to%20manage,on%20what%20matters%20most%3A%20teaching)).
- ❌ **Project proposals/assignment:** There is no dedicated capstone or project allocation module. Students cannot "propose" a project in the system by default. Academics could manually record project assignments as courses or tasks, but no streamlined workflow exists.
- ❌ **Tracking/Feedback:** It can record final grades, but lacks a workflow for iterative project progress tracking or feedback cycles.
- ❌ **NDA/Compliance:** None.
- ❌ **Automated allocation:** None.
- ❌ **Double marking:** ERPNext is not designed for secondary marking; instructors enter final grades directly.

**Higher Ed Fit:** ERPNext Education is useful for overall student/admin management, especially in institutions seeking a full ERP solution. Its strengths are in administration (fees, HR, etc.) rather than academic project workflows. Using it for final-year projects would likely involve workarounds (recording projects as courses/tasks manually). It does not inherently solve the key needs like proposal review or project progress tracking for capstones, so its direct coverage of the requirements is limited.

## "Generic" SaaS Project Tools (e.g. Asana, Trello, Monday.com) – *Commercial/SaaS*
**Description:** Mainstream project/task-management platforms (Asana, Trello, ClickUp, etc.) are flexible cloud tools used by teams including in education ([softivizes.com](https://softivizes.com/articles/best-project-management-software-higher-education/#:~:text=Various%20software%20tools%20stand%20out,oriented%20board)). They allow creating project boards, task lists, deadlines, and communications. For example, Trello's card/board metaphor or Asana's task lists can list project topics, deadlines, and to-do items.

**Cost/Licensing:** Typically subscription-based (per-user pricing); free tiers offer limited users or features.

**Key Features:**
- ✔ **Task tracking and collaboration:** Faculty can create "project" boards or tasks and assign students. Students can comment, upload files, and move tasks through stages. This can partially handle **track project progress** and basic feedback.<br>
- ✔ **Project listings:** Academics could manually post proposed projects as tasks/cards for students to browse (fulfilling "View proposed projects" to some extent). Students could claim a project by joining a board or claiming a card, though the workflow is manual.
- ❌ **Proposal review workflow:** No formal proposal submission/approval process. Any acceptance would be external (email, etc.).
- ❌ **Automated allocation:** These tools do not support auto-assignment by GPA; allocation is manual.
- ❌ **NDA/Insurance:** No features for compliance tracking.
- ❌ **Blind double marking:** Not available; these tools are not designed for grading.

**Higher Ed Fit:** These SaaS platforms are easy to adopt (many students/faculty already know Trello/Asana) and are great for basic collaboration/shared task lists. They fit general group work contexts well, but they lack academic rigidity. Their pros are usability and minimal setup; cons are the need for manual processes for everything outside basic task tracking. In practice, a department might use Trello to manage parts of capstone projects, but they'd still need extra measures for formal approval, grading, and compliance.

## Moodle Workshop/Assignment (Moodle, Canvas) – *Open Source / Commercial LMS*
**Description:** Common Learning Management Systems (e.g. Moodle, Canvas) can be bent to some project uses. For example, Moodle's "Workshop" activity allows peer review of student submissions, and assignments can be set to anonymous (blind) grading. Academics could use a forum or database activity to post project options, and an assignment activity for final reports.

**Key Features:**
- ✔ **Submission & grading:** Students can submit proposals/documents, and instructors can grade them online with feedback. Moodle's assignment module supports blind marking (by hiding names) and even workshop peer review.
- ❌ **Project listings & proopsal submission:** Out-of-the-box, none of these LMS provide a project pool or formal proposal/approval workflow. They are content-centric (courses, quizzes) not proposal-centric.
- ❌ **Automated allocation/NDA:** No support.

**Higher Ed Fit:** Most universities already use an LMS like Moodle or Canvas, so this would be an "internal" solution with no extra cost. However, forcing capstone project workflows into an LMS is cumbersome. It could cover **submission** (students turn in proposals and final reports) and **blind grading**, but faculty would have to manually configure activities to approximate project management (e.g. post all options as a forum or wiki, use assignment plugin for submission). This approach can meet some needs (tracking submissions, providing feedback) but would not truly automate proposal review or allocation.

## MarkUs (University of Waterloo) – *Open Source (University-built)*
**Description:** MarkUs is an open-source assignment submission and grading platform originally developed by the University of Waterloo ([cs.uwaterloo.ca](https://cs.uwaterloo.ca/twiki/view/ISG/MarkUs#:~:text=MarkUs%20%20is%20an%20open,described%20and%20posted%20at%20MarkUsScripts)). It allows students to upload assignments, instructors to apply rubric or free-form marking, and students to view graded feedback online ([cs.uwaterloo.ca](https://cs.uwaterloo.ca/twiki/view/ISG/MarkUs#:~:text=MarkUs%20%20is%20an%20open,described%20and%20posted%20at%20MarkUsScripts)). It excels at supporting multiple graders and releasing marks.

**Cost/Licensing:** Free/open-source.

**Key Features:**
- ✔ **Online Submission & Feedback:** Students submit work and instructors annotate or rubric-mark it online ([cs.uwaterloo.ca](https://cs.uwaterloo.ca/twiki/view/ISG/MarkUs#:~:text=MarkUs%20%20is%20an%20open,described%20and%20posted%20at%20MarkUsScripts)). Feedback and marks are then released to students.
- ✔ **Rubric and Multiple Graders:** Supports customizable rubric schemes and assigning submissions to different graders ([cs.uwaterloo.ca](https://cs.uwaterloo.ca/twiki/view/ISG/MarkUs#:~:text=%2A%20Rubric%20%284,Checking%20Grading%20Progress)). This can facilitate **blind double marking** – instructors can mark independently (the system can hide identities) and then reconcile grades later.
- ❌ **Project proposals/allocation:** MarkUs is not designed for managing project proposals or assignments; it assumes a fixed assignment for all students.
- ❌ **NDA/Insurance:** No support.

**Higher Ed Fit:** MarkUs is specifically targeted at coursework and lab assignments (especially coding projects). It does not cover the end-to-end capstone workflow, but it directly addresses the double-marking/feedback aspect. A department could use MarkUs just for the grading portion of projects: after an adviser collects projects offline, the actual marking could be done in MarkUs using its blind-rubric features. Its advantage is a proven, academic focus on grading; drawback is that proposal, allocation, and tracking must be handled elsewhere.

## Summary of Feature Coverage

- **OpenEduCat** – Covers *≈80%* of needs: proposal submission, project assignment, progress tracking, feedback (very close fit) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)). Gaps: NDA/industry compliance, automated GPA-based allocation, and formal blind grading are not built in.
- **OpenProject** – Covers basic project/task tracking and collaboration ([en.wikipedia.org](https://en.wikipedia.org/wiki/OpenProject#:~:text=Users%20can%20create%20and%20track,a%20configurable%20and%20flexible%20system)), but *<50%* of needs. Major gaps: no academic proposal workflow, no compliance tracking, no marking support.
- **ERPNext/Moodle/LMS** – Each is partial. ERPNext covers admin tasks (admissions/grades) ([frappe.io](https://frappe.io/erpnext/for-education#:~:text=A%20comprehensive%20solution%20to%20manage,on%20what%20matters%20most%3A%20teaching)) but not project workflows; Moodle covers submissions and anonymous feedback but not proposals or allocation. Together they might cover 40–50%. They'd require manual bridging.
- **Generic SaaS (Trello, Asana)** – Coverage ~30%: good for high-level task tracking and collaboration, but lacks academic-specific features.
- **MarkUs** – Covers *100%* of the **blind marking** requirement and feedback, but 0% of the proposal/allocation aspects.

# Build vs Buy Recommendation

Building a custom system in-house from scratch would be a major undertaking. The requirements span proposal submission, workflow approvals, project tracking, grading, and compliance – effectively a full academic project management ecosystem. Given this complexity, it would be far more efficient to adopt and extend an existing platform rather than "reinvent the wheel." Thus, a **Buy (with customization)** approach is advisable: select or assemble software that covers most functionality and fill remaining gaps with targeted development or additional tools.

The best single solution match is **OpenEduCat**, which already implements much of the project/thesis workflow ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)). Using OpenEduCat (or a similar education ERP) "out of the box" could satisfy roughly 70–80% of needs, especially the proposal submission, assignment, tracking, and feedback aspects. The remaining features (NDA management, auto-allocation, blind double marking) would then be added on top of this system. For example, NDA and insurance tracking could be handled by adding a document/cache module or linking to a compliance DB; automated allocation could be scripted (leveraging the students' GPA data) and integrated as a plugin; and double-blind marking could be handled by integrating a grading tool (like MarkUs) or developing a marking workflow within the system.

**Recommendation:** Use an existing higher-ed platform (such as OpenEduCat) and customize it. This "buy or extend" approach minimizes risk and leverages community/support resources. The mixed solution might be:
- **Core**: OpenEduCat or ERPNext for project and student management.
- **Supplement**: Trello/Asana for ad-hoc collaboration (optional), and MarkUs (or LMS grading) for the formal blind marking stage.
- **Custom Modules**: A small custom app for NDAs/insurance, and a matching script for GPA-based allocation.

By contrast, building all features from the ground up would be very costly and redundant given the solid groundwork in existing products. Therefore, **Buy-and-customize** is the sensible recommendation.

# Best Match and Gaps

**Best fit:** OpenEduCat (Odoo-based) meets the broadest range of requirements natively. It covers proposals, assignments, and feedback (≈80% coverage) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)).

**Coverage estimate:** OpenEduCat (80%), with generic tools (30–50%), and specialized tools (MarkUs ~20% on marking) so no single product covers 100%. A combination is needed.

**Major gaps:** None of the ready solutions handle all of **NDA/industry compliance tracking**, **automated GPA-based allocation**, or **formal blind double marking/reconciliation**. These would require custom development or integration (e.g. a workflow for entering NDA details, an allocation algorithm, and a grading platform). These are the areas to focus on after choosing a base system. For example, after adopting OpenEduCat, the custom work would center on adding:
1. NDA/Insurance form handling (could be a simple Odoo document module),
2. An allocation script (Odoo allows custom Python code to assign records by GPA),
3. A blind-marking process (possibly integrating MarkUs or using an LMS's anonymous grading tied into OpenEduCat's grade records).
MARKDOWN,
                        'timestamp' => $now->copy()->subDays(3)->addHours(4),
                    ],
                ],
                'proposed_application' => [
                    'name' => 'Final Year Project Portal',
                    'short_description' => 'Manages academic project proposals, student matching, and assessment workflows.',
                    'overview' => 'Coordinates project proposals from academics, student submissions, automated allocation, progress tracking, and blind double marking for final year dissertations.',
                ],
            ],
        ];

        foreach ($newApplicationConversations as $data) {
            $conversation = Conversation::factory()->create([
                'user_id' => $requestingUser->id,
                'application_id' => null,
                'title' => $data['title'] ?? null,
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
