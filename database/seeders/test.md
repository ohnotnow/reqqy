# Solutions and Options

## OpenEduCat (Odoo Education – Odoo Community Association) – *Open Source*
**Description:** OpenEduCat is an open-source, Odoo-based campus management system designed specifically for higher education ([www.spacebasic.com](https://www.spacebasic.com/blogs/open-source-campus-management-system#:~:text=4)). It includes a dedicated **Thesis/Project Management** module that lets students submit project proposals online and tracks the entire workflow. It provides proposal submission with status updates ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)), supervisor/project advisor assignment, document management (drafts, feedback, final submissions) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)), online review/feedback tools ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)), and progress dashboards with automated reminders ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)). In essence, it supports academics proposing projects, receiving student proposals, approving/rejecting proposals, assigning students, and giving feedback via the system.

**Cost/Licensing:** OpenEduCat Community Edition is free and open-source (LGPL-3) ([doc.openeducat.org](https://doc.openeducat.org/legal/legal.html#:~:text=OpenEduCat%20Community%20Edition%20is%20licensed,License%20and%20the%20compatibility%20matrix)). The community version has no licensing fee; an optional Enterprise version is available for paid support and premium features.

**Key Features (vs. requirements):**
- ✔ **Project proposal workflow:** Students can submit proposals electronically; academics review, comment, and approve/reject within the system ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)).
- ✔ **Supervisor/Advisor assignment:** Academics can assign supervisors or mentors based on student interests ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)). (Fulfills “assign projects to students” if supervisors ≈ projects.)
- ✔ **Progress tracking:** Built-in dashboards show milestone progress, deadlines, and reminders ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)).
- ✔ **Feedback:** Supervisors and reviewers can annotate submissions and provide feedback online ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)).

- ❌ **Missing aspects:** No built-in module for tracking NDAs or insurance for industry projects. Automated student–project allocation (e.g. by GPA) isn’t provided out-of-the-box. Double-blind marking and mark reconciliation must be handled separately (it offers review tools but no formal blind‐marking workflow).

**Higher Ed Fit:** Because it is tailored for universities, OpenEduCat aligns well with academic workflows ([www.spacebasic.com](https://www.spacebasic.com/blogs/open-source-campus-management-system#:~:text=4)). It covers most of the standard final-year project lifecycle. Its modular design (Odoo-based) means campuses can add or customize features (e.g. grading rules, compliance) ([www.spacebasic.com](https://www.spacebasic.com/blogs/open-source-campus-management-system#:~:text=,and%20HR%20tools%20for%20staff)). However, being a broad ERP/LMS, it may require development or custom modules to handle very specific needs (NDA tracking, auto-allocation algorithms, or exam-style blind double marking). Overall, OpenEduCat would likely meet a large portion (~80%) of the requirements, making it a strong candidate as a starting point.

## OpenProject (OpenProject GmbH) – *Open Source (Cloud/Self-hosted)*
**Description:** OpenProject is an open-source web-based project management tool (GPLv3) for cloud or on-premise use ([en.wikipedia.org](https://en.wikipedia.org/wiki/OpenProject#:~:text=OpenProject%20is%20project%20management%20software,free%2C%20community%20edition%20and%20a)). It supports creating and tracking projects with features like Gantt charts, task management, Kanban boards, calendars, and meeting agendas ([en.wikipedia.org](https://en.wikipedia.org/wiki/OpenProject#:~:text=Users%20can%20create%20and%20track,a%20configurable%20and%20flexible%20system)). OpenProject emphasizes transparency and collaboration; users can form projects with timelines and tasks, and team members can track progress and communicate within these projects. It is widely used in business and education settings; for example, one survey ranks OpenProject among the top project-management solutions at universities ([www.openproject.org](https://www.openproject.org/blog/project-management-software-universities/#:~:text=OpenProject%20is%20ranked%20here%20among,and%20commercial%20solutions%20in%20blue)).

**Cost/Licensing:** The Community Edition is free (open-source under GPLv3 ([en.wikipedia.org](https://en.wikipedia.org/wiki/OpenProject#:~:text=OpenProject%20is%20project%20management%20software,free%2C%20community%20edition%20and%20a))). Paid hosting or Enterprise support plans are available from the vendor.

**Key Features (vs. requirements):**
- ✔ **Project/task tracking:** Academics could set up each project as an OpenProject “project” or task list. Students and staff can view tasks, milestones, and progress. This supports general progress tracking, milestone scheduling, and collaboration.
- ✔ **Collaboration/Feedback:** Team members can comment on tasks and documents, facilitating feedback and communication.
- ❌ **Project proposals:** There’s no formal proposal submission workflow. Academics could post project topics as tasks, but there is no built-in accept/reject or review process for proposals.
- ❌ **Automated assignment:** No built-in algorithm for student allocation (all assignment of tasks to users is manual).
- ❌ **NDA compliance:** No module for NDAs or external project compliance documentation.
- ❌ **Blind double marking:** OpenProject has no exam/marking features; it does not support anonymous or double blind grading.

**Higher Ed Fit:** OpenProject is a general-purpose PM tool. Its flexibility means universities use it for research project tracking or administrative planning (it is “top 5” in university project management tools ([www.openproject.org](https://www.openproject.org/blog/project-management-software-universities/#:~:text=OpenProject%20is%20ranked%20here%20among,and%20commercial%20solutions%20in%20blue))). Advantages: highly configurable, strong for timeline/task management, and open-source (so code can be extended). Limits: It is not tailored to student projects; implementing proposal submission or marking workflows would require major customization. It could handle the “project tracking” portion well, but falls short on academic-specific features.

## ERPNext (Frappe/ERPNext) – *Open Source*
**Description:** ERPNext is an open-source ERP system with an Education module. The education app manages student admissions, records, fees, course assignments, and learning outcomes ([frappe.io](https://frappe.io/erpnext/for-education#:~:text=A%20comprehensive%20solution%20to%20manage,on%20what%20matters%20most%3A%20teaching)). It provides integrated portals for students, faculty, and parents. Although primarily an ERP (finance/HR), ERPNext includes tools like assignment records and grade entry.

**Cost/Licensing:** ERPNext is free and open-source (GNU GPL), with optional paid cloud hosting or enterprise services.

**Key Features:**
- ✔ **Student records & administration:** Handles admissions, enrollment, scheduling, attendance, grading, and fee management ([frappe.io](https://frappe.io/erpnext/for-education#:~:text=A%20comprehensive%20solution%20to%20manage,on%20what%20matters%20most%3A%20teaching)).
- ❌ **Project proposals/assignment:** There is no dedicated capstone or project allocation module. Students cannot “propose” a project in the system by default. Academics could manually record project assignments as courses or tasks, but no streamlined workflow exists.
- ❌ **Tracking/Feedback:** It can record final grades, but lacks a workflow for iterative project progress tracking or feedback cycles.
- ❌ **NDA/Compliance:** None.
- ❌ **Automated allocation:** None.
- ❌ **Double marking:** ERPNext is not designed for secondary marking; instructors enter final grades directly.

**Higher Ed Fit:** ERPNext Education is useful for overall student/admin management, especially in institutions seeking a full ERP solution. Its strengths are in administration (fees, HR, etc.) rather than academic project workflows. Using it for final-year projects would likely involve workarounds (recording projects as courses/tasks manually). It does not inherently solve the key needs like proposal review or project progress tracking for capstones, so its direct coverage of the requirements is limited.

## “Generic” SaaS Project Tools (e.g. Asana, Trello, Monday.com) – *Commercial/SaaS*
**Description:** Mainstream project/task-management platforms (Asana, Trello, ClickUp, etc.) are flexible cloud tools used by teams including in education ([softivizes.com](https://softivizes.com/articles/best-project-management-software-higher-education/#:~:text=Various%20software%20tools%20stand%20out,oriented%20board)). They allow creating project boards, task lists, deadlines, and communications. For example, Trello’s card/board metaphor or Asana’s task lists can list project topics, deadlines, and to-do items.

**Cost/Licensing:** Typically subscription-based (per-user pricing); free tiers offer limited users or features.

**Key Features:**
- ✔ **Task tracking and collaboration:** Faculty can create “project” boards or tasks and assign students. Students can comment, upload files, and move tasks through stages. This can partially handle **track project progress** and basic feedback.<br>
- ✔ **Project listings:** Academics could manually post proposed projects as tasks/cards for students to browse (fulfilling “View proposed projects” to some extent). Students could claim a project by joining a board or claiming a card, though the workflow is manual.
- ❌ **Proposal review workflow:** No formal proposal submission/approval process. Any acceptance would be external (email, etc.).
- ❌ **Automated allocation:** These tools do not support auto-assignment by GPA; allocation is manual.
- ❌ **NDA/Insurance:** No features for compliance tracking.
- ❌ **Blind double marking:** Not available; these tools are not designed for grading.

**Higher Ed Fit:** These SaaS platforms are easy to adopt (many students/faculty already know Trello/Asana) and are great for basic collaboration/shared task lists. They fit general group work contexts well, but they lack academic rigidity. Their pros are usability and minimal setup; cons are the need for manual processes for everything outside basic task tracking. In practice, a department might use Trello to manage parts of capstone projects, but they’d still need extra measures for formal approval, grading, and compliance.

## Moodle Workshop/Assignment (Moodle, Canvas) – *Open Source / Commercial LMS*
**Description:** Common Learning Management Systems (e.g. Moodle, Canvas) can be bent to some project uses. For example, Moodle’s “Workshop” activity allows peer review of student submissions, and assignments can be set to anonymous (blind) grading. Academics could use a forum or database activity to post project options, and an assignment activity for final reports.

**Key Features:**
- ✔ **Submission & grading:** Students can submit proposals/documents, and instructors can grade them online with feedback. Moodle’s assignment module supports blind marking (by hiding names) and even workshop peer review.
- ❌ **Project listings & proopsal submission:** Out-of-the-box, none of these LMS provide a project pool or formal proposal/approval workflow. They are content-centric (courses, quizzes) not proposal-centric.
- ❌ **Automated allocation/NDA:** No support.

**Higher Ed Fit:** Most universities already use an LMS like Moodle or Canvas, so this would be an “internal” solution with no extra cost. However, forcing capstone project workflows into an LMS is cumbersome. It could cover **submission** (students turn in proposals and final reports) and **blind grading**, but faculty would have to manually configure activities to approximate project management (e.g. post all options as a forum or wiki, use assignment plugin for submission). This approach can meet some needs (tracking submissions, providing feedback) but would not truly automate proposal review or allocation.

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
- **ERPNext/Moodle/LMS** – Each is partial. ERPNext covers admin tasks (admissions/grades) ([frappe.io](https://frappe.io/erpnext/for-education#:~:text=A%20comprehensive%20solution%20to%20manage,on%20what%20matters%20most%3A%20teaching)) but not project workflows; Moodle covers submissions and anonymous feedback but not proposals or allocation. Together they might cover 40–50%. They’d require manual bridging.
- **Generic SaaS (Trello, Asana)** – Coverage ~30%: good for high-level task tracking and collaboration, but lacks academic-specific features.
- **MarkUs** – Covers *100%* of the **blind marking** requirement and feedback, but 0% of the proposal/allocation aspects.

# Build vs Buy Recommendation

Building a custom system in-house from scratch would be a major undertaking. The requirements span proposal submission, workflow approvals, project tracking, grading, and compliance – effectively a full academic project management ecosystem. Given this complexity, it would be far more efficient to adopt and extend an existing platform rather than “reinvent the wheel.” Thus, a **Buy (with customization)** approach is advisable: select or assemble software that covers most functionality and fill remaining gaps with targeted development or additional tools.

The best single solution match is **OpenEduCat**, which already implements much of the project/thesis workflow ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)) ([openeducat.org](https://openeducat.org/feature-thesis-management-system#:~:text=)). Using OpenEduCat (or a similar education ERP) “out of the box” could satisfy roughly 70–80% of needs, especially the proposal submission, assignment, tracking, and feedback aspects. The remaining features (NDA management, auto-allocation, blind double marking) would then be added on top of this system. For example, NDA and insurance tracking could be handled by adding a document/cache module or linking to a compliance DB; automated allocation could be scripted (leveraging the students’ GPA data) and integrated as a plugin; and double-blind marking could be handled by integrating a grading tool (like MarkUs) or developing a marking workflow within the system.

**Recommendation:** Use an existing higher-ed platform (such as OpenEduCat) and customize it. This “buy or extend” approach minimizes risk and leverages community/support resources. The mixed solution might be:
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
3. A blind-marking process (possibly integrating MarkUs or using an LMS’s anonymous grading tied into OpenEduCat’s grade records).

