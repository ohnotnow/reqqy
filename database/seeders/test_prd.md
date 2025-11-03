# Product Requirements Document: Final Year Project Portal

## 1. Executive Summary

The Final Year Project Portal is a comprehensive web-based system designed to modernize dissertation and capstone project management for the Computer Science department. Currently, the department manages final year projects through fragmented spreadsheets, email workflows, and manual processes, leading to lost proposals, limited progress visibility, and cumbersome assessment procedures.

This application will provide a centralized platform where academics can propose projects, students can browse opportunities and submit proposals, administrators can manage allocations and track progress, and assessors can conduct blind double marking with built-in reconciliation tools. The system will also handle compliance requirements for industry-partnered projects, including NDA tracking and insurance verification.

**Target Users:** Academic staff (project supervisors), final year and masters students, programme administrators, and external industry partners.

**Core Problem:** Eliminate administrative overhead, improve visibility into project workflows, ensure compliance for industry partnerships, and streamline the assessment process from proposal to final marking.

## 2. Goals and Objectives

- **Reduce Administrative Overhead:** Eliminate manual spreadsheet management and email-based proposal tracking by centralizing all project workflows in a single system.
- **Improve Visibility:** Provide real-time dashboards for programme directors to monitor project progress, allocation status, and at-risk students across cohorts.
- **Automate Compliance:** Ensure all industry-partnered projects meet NDA and insurance requirements before student assignment, with automated reminders for document expiry.
- **Streamline Assessment:** Implement anonymous submission handling and parallel blind double marking workflows with automatic reconciliation for discrepancies.
- **Enhance Student Experience:** Enable students to easily browse projects by research area, submit proposals, track milestones, and receive structured feedback from supervisors.
- **Support Flexible Allocation:** Allow both manual coordinator assignment and automated GPA-based allocation for oversubscribed projects.

## 3. User Personas

### Academic Staff (Project Supervisors)
**Profile:** Lecturers, senior lecturers, and professors who propose and supervise final year projects.

**Needs:**
- Propose multiple project ideas with clear descriptions and prerequisites
- Review student applications and provide feedback on proposals
- Track student progress through milestones
- Provide structured feedback and flag at-risk projects
- Participate in marking and moderation workflows

**Pain Points:** Currently lose track of proposals in email threads, lack visibility into student progress between meetings, and spend excessive time on manual grading reconciliation.

### Students (Final Year Undergraduates & Masters)
**Profile:** Students in their final year or completing a taught masters programme who must complete a dissertation or capstone project.

**Needs:**
- Browse available projects filtered by research area, difficulty, and type
- Submit expressions of interest or propose their own projects
- Track milestones and deadlines throughout the project lifecycle
- Upload deliverables and receive timely feedback
- Understand requirements for industry-partnered projects

**Pain Points:** Difficulty finding projects matching their interests, unclear on progress expectations, and anxiety about supervisor availability.

### Programme Administrators
**Profile:** Department administrators and coordinators responsible for managing the project allocation process and ensuring compliance.

**Needs:**
- Oversee the entire allocation process with both manual and automated options
- Monitor progress across all students in a cohort
- Manage compliance documentation for external projects
- Coordinate marking, moderation, and grade reconciliation
- Generate reports for quality assurance and programme review

**Pain Points:** Currently juggle multiple spreadsheets, chase missing compliance documents, and manually reconcile marks with tight deadlines.

### External Partners (Industry - Phase 2)
**Profile:** Industry professionals offering real-world projects to students.

**Needs:**
- Submit project briefs aligned with business objectives
- Review student applications
- Provide placement supervision and feedback
- Ensure confidentiality and compliance requirements are met

**Pain Points:** Unclear on university processes, concerned about IP protection, and frustrated by slow turnaround times.

## 4. Functional Requirements

### Must Have (P0)

#### Project Proposal Management
- Academics can create project listings with title, description, prerequisites, research area, difficulty level, and capacity (individual/group)
- Draft/publish workflow with approval gates for quality assurance
- Support for both staff-proposed projects and student-initiated proposals
- Ability to mark projects as active, full, or closed

#### Student Proposal & Browsing
- Students can browse available projects with filtering (research area, difficulty, type)
- Search functionality for keywords in titles and descriptions
- Submit expressions of interest or full project proposals
- View project details including supervisor information and prerequisites

#### Allocation Workflows
- Manual assignment by programme coordinator
- Automated GPA-based allocation for oversubscribed projects
- Waiting list management for popular projects
- Email notifications for assignment confirmations

#### Progress Tracking
- Configurable milestone stages (proposal submission, ethics approval, interim report, final submission)
- Students upload deliverables and progress updates
- Supervisors provide structured feedback and track student engagement
- Automated alerts for overdue milestones

#### Blind Double Marking
- Anonymous submission handling (system strips identifiable information from uploaded files)
- Parallel marking workflows assigned to two independent markers
- Grade entry with rubric-based or free-form marking
- Automated reconciliation process highlighting discrepancies beyond a configurable threshold (e.g., 10%)
- Moderation workflow for disputed marks with full audit trail

### Should Have (P1)

#### Industry Project Compliance
- Dedicated workflow for external/industry-partnered projects
- Document repository for uploading NDAs, insurance certificates, and partner agreements
- Approval checkpoints before student assignment
- Automated reminders for document expiry and renewal (e.g., 30 days before expiry)
- Status indicators: Pending Compliance, Approved, Expired

#### Enhanced Feedback & Communication
- In-app messaging between students and supervisors
- Ability to schedule and log supervision meetings
- Progress notes visible to student and supervisor
- Email digest summaries of activity

#### Reporting & Analytics
- Programme director dashboard with cohort completion rates
- At-risk project identification (e.g., overdue milestones, low engagement)
- Allocation statistics (projects per supervisor, student distribution by research area)
- Export to CSV/Excel for institutional reporting

### Nice to Have (P2)

- Integration with institutional plagiarism detection tools
- Peer feedback/review for group projects
- Automated project recommendation engine based on student transcript and interests
- External partner portal (self-service project submission and student application review)
- Video conferencing integration for virtual supervision meetings
- Mobile app for students to check milestones and receive notifications

## 5. Non-Functional Requirements

### Performance
- Page load times under 2 seconds for browsing and dashboards
- Support for concurrent uploads of large documents (up to 50MB per file)
- Bulk allocation processing for cohorts of 200+ students within 5 minutes

### Security
- Role-based access control (students, academic staff, coordinators, external partners, admin)
- Encryption for uploaded documents containing sensitive information
- Audit logging for all grade changes and moderation decisions
- GDPR compliance for student and partner data
- Secure file storage with access controls preventing cross-student visibility

### Scalability
- Support for multiple departments and cohorts simultaneously
- Handle growth from 200 to 1000+ students over 3 years
- Archiving of historical project data without performance degradation

### Compatibility
- Mobile-responsive design (Bootstrap or Tailwind CSS)
- Support for modern browsers (Chrome, Firefox, Safari, Edge - latest 2 versions)
- Accessibility compliance (WCAG 2.1 AA standard)

### Integration
- SSO via institutional identity management (SAML/OAuth)
- Student ID lookup from student records system (SITS or equivalent)
- Calendar integration for milestone reminders (Outlook/Teams)

### Data Retention
- Active projects retained for duration of academic year + 1 year
- Archived projects retained for 7 years (institutional policy alignment)
- Secure deletion procedures for expired data

## 6. User Stories

### Academic Staff
- **As a supervisor**, I want to propose multiple projects with detailed descriptions so students have diverse options aligned with my research interests.
- **As a supervisor**, I want to review student proposals and provide feedback so I can guide students toward feasible and valuable projects.
- **As a marker**, I want to grade submissions anonymously without seeing peer marks so I can provide unbiased assessment.
- **As a supervisor**, I want to track student progress with milestone dashboards so I can identify and support struggling students early.

### Students
- **As a student**, I want to browse projects by research area so I can find topics matching my interests and career goals.
- **As a student**, I want to propose my own project idea so I can pursue a topic I'm passionate about.
- **As a student**, I want to receive automated milestone reminders so I don't miss critical deadlines.
- **As a student**, I want to see all my feedback in one place so I can track my progress and understand expectations.

### Programme Administrators
- **As a coordinator**, I want to auto-allocate students by GPA when projects are oversubscribed so I can fairly distribute opportunities without manual negotiation.
- **As an administrator**, I want to ensure industry projects have valid NDAs before assignment so the university and students are legally protected.
- **As a coordinator**, I want to monitor completion rates and identify at-risk projects so I can intervene proactively.
- **As a programme director**, I want to generate cohort analytics reports so I can demonstrate quality assurance to external reviewers.

### External Partners (Phase 2)
- **As an industry partner**, I want to submit project briefs online so I can offer real-world opportunities to students efficiently.
- **As an industry partner**, I want to review student applications and CVs so I can select candidates with relevant skills.

## 7. Technical Considerations

### Recommended Tech Stack
- **Framework:** Laravel 11+ (proven for academic applications, excellent ecosystem)
- **Frontend:** Livewire 3 with Blade templates (reduces complexity vs. SPA, good for form-heavy apps)
- **Database:** PostgreSQL or MySQL (relational data model fits project/student relationships)
- **File Storage:** AWS S3 or Laravel Vapor for scalable document storage
- **Authentication:** Laravel Breeze or Jetstream with SSO integration via Socialite
- **Queue System:** Laravel Queues with Redis for processing bulk allocations and notifications
- **Email:** Laravel Mail with configurable SMTP or SES

### Architecture Patterns
- **Multi-tenancy (Optional):** If supporting multiple departments, consider tenant isolation at database level
- **Event-Driven:** Use Laravel Events for notifications (e.g., ProjectAssigned, MilestoneOverdue)
- **Policy-Based Authorization:** Laravel Policies for fine-grained access control (e.g., CanViewProject, CanGradeSubmission)

### Third-Party Integrations
- **SSO Provider:** Institutional SAML/OAuth (e.g., Microsoft Azure AD, Shibboleth)
- **Student Records API:** Integration with SITS or student information system for ID validation and GPA lookup
- **Calendar API:** Microsoft Graph API for Outlook/Teams calendar sync
- **Document Processing (Phase 2):** OCR for NDA/insurance certificate verification automation

### Data Model Highlights
- **Projects:** belongs to Academic, has many Students through Allocations
- **Students:** has one Allocation, has many Milestones, has many Submissions
- **Submissions:** polymorphic (can be milestone deliverables or final project)
- **Grades:** belongs to Submission, belongs to Marker (with blind flag)
- **ComplianceDocuments:** belongs to Project (for industry projects)

## 8. Out of Scope

The following features are explicitly excluded from the MVP (Version 1.0):

- **Plagiarism Detection Integration:** Use existing institutional tools (e.g., Turnitin) outside the system
- **Video Conferencing Tools:** Use existing platforms (Teams, Zoom); no in-app video meetings
- **External Partner Portal:** Industry partners will submit projects via coordinator in MVP; self-service portal deferred to Phase 2
- **Ethics Approval Workflow:** Ethics applications managed in separate institutional system; status can be recorded but not processed
- **Automated Project Recommendation Engine:** Initial version uses manual browsing/search; ML-based recommendations in future
- **Peer Review for Group Projects:** Focus on individual projects in MVP; group project features deferred
- **Version Control for Proposals:** Proposals are fixed after approval; editing after publication not supported initially
- **Multi-Language Support:** English-only for MVP

## 9. Open Questions

### Allocation & Workflow
1. **GPA Allocation Threshold:** What is the minimum GPA difference to trigger automated allocation vs. allowing student preference? (e.g., allocate by GPA only if project is 2x oversubscribed)
2. **Supervisor Capacity:** Should the system enforce a maximum number of students per supervisor? If so, what is the limit?
3. **Project Visibility:** Should students see supervisor availability/capacity when browsing projects to inform their choices?

### Assessment & Marking
4. **Reconciliation Threshold:** What percentage/point difference triggers mandatory moderation? (e.g., marks differ by >10%)
5. **Blind Marking Scope:** Should interim deliverables also be blind-marked, or only final submissions?
6. **Marking Rubrics:** Should the system provide predefined rubrics, or allow each supervisor to define custom criteria?

### Compliance & Industry Projects
7. **NDA Templates:** Does the university have standard NDA templates that should be pre-populated in the system?
8. **Insurance Verification:** Who is responsible for verifying insurance documents—administrators or automated checks?
9. **Partner Onboarding:** What information must be collected from industry partners before they can propose projects?

### Milestones & Progress Tracking
10. **Milestone Granularity:** How many configurable milestones are needed? (e.g., 3-5 stages vs. highly customizable per project)
11. **At-Risk Criteria:** What specific conditions define an "at-risk" project? (e.g., 2 overdue milestones, no supervisor contact in 4 weeks)

### Technical Integration
12. **Student Records Integration:** Is there an API available for real-time GPA lookups, or will this be a manual import?
13. **SSO Provider:** Which SSO provider does the institution use? (Azure AD, Shibboleth, etc.)
14. **Data Retention:** Confirm institutional policy for archiving completed projects and student data post-graduation.

---

**Document Version:** 1.0
**Last Updated:** 2025-11-03
**Author:** Reqqy PRD System
**Status:** Draft for Review
