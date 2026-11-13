<?php
/*
 * The administrator guide. Pictures: assets/admin_assets/images/guide/admin/.
 */

return [
    'title'  => 'Administrator guide',
    'folder' => 'admin',
    'intro'  => 'How to set UPTMS up for a department and how to run it every day. Start with "Set up in the right order": a batch needs a department and a session, a group needs students, and nothing can be approved before a thesis committee exists.',
    'sections' => [

        [
            'id' => 'order', 'icon' => 'list-ol', 'title' => 'Set up in the right order (first time)',
            'summary' => 'Do these steps once, in this order. Each one needs the one before it.',
            'steps' => [
                '**Settings**: write the institution and department name, the group size and the number of committee signatures. If you want the login details of new users to be emailed, fill the **Email (SMTP)** box, press [Send test email] and then switch **Send emails** on.',
                '**Academic Sessions**: add the session (for example "Spring 2026") and mark the current one.',
                '**Batches**: add each batch (name, code, department, shift, admission session).',
                '**Teachers** and **Students**: add them one by one or import them from a CSV file. Students need a batch.',
                '**Research Categories** and **Research Tags**: the subject areas and the keywords the students will choose from.',
                '**Thesis Committees**: make at least one committee and add its teachers. Tick **Can approve** for the members who sign. Without a committee nobody can approve a topic or a finish.',
                '**Groups/Teams**: students request their own groups, or you add them. Approve the requests.',
                '**Assign Supervisor** (you or the committee): give each approved group its supervisor.',
                'Tell the students and teachers their email address. Every new account gets the default password of its role (**wub@student123**, **wub@teacher123**, **wub@admin123**) and must choose a new one at the first login. Ask them to read their own **User Guide**.',
            ],
            'tips' => [
                'The departments list is read only because UPTMS is set for one department. The switch for several departments is in the .env file.',
                'Change the placeholder teacher emails before real use. The old accounts that were made before the default passwords keep their own passwords.',
            ],
        ],

        [
            'id' => 'login', 'icon' => 'box-arrow-in-right', 'title' => 'Sign in, profile and password',
            'summary' => 'Sign in on the same login page as everybody else. Your profile page lets you change your picture, name, phone and password.',
            'steps' => [
                'Open **My Profile**: press [Update Profile] (1) to save your details and [Update Password] (2) to change the password. A new administrator starts with **wub@admin123** and is asked to change it at the first login.',
                'Several administrators can exist (see **Admins**). The last active administrator cannot be deleted or deactivated.',
            ],
            'image' => 'profile.jpg',
            'link' => ['Open My Profile', 'admin/profile.php'],
        ],

        [
            'id' => 'dashboard', 'icon' => 'speedometer2', 'title' => 'The dashboard and the menu',
            'summary' => 'The dashboard shows the numbers of the system (students, teachers, groups, research) and what needs attention. The left menu is the map of the system.',
            'steps' => [
                '**Research Library**: all research and the research tools (a second small menu inside).',
                '**University**: teachers, students, departments, sessions, batches, groups/teams and thesis committees.',
                '**Evaluation**, **Admins**, **Reports**, **Settings** and **My Profile**.',
                'A small number on a menu item counts what waits (for example new group requests).',
            ],
            'image' => 'dashboard.jpg',
            'link' => ['Open the dashboard', 'admin/index.php'],
        ],

        [
            'id' => 'settings', 'icon' => 'gear', 'title' => 'Settings',
            'summary' => 'The rules of the whole system are set on one page.',
            'steps' => [
                '**Institution**: the institution name, the department name and the support email. They are printed on the reports.',
                '**Groups/Teams**: the smallest and the largest group (normally 2 and 3).',
                '**Thesis committee signatures**: how many ticked committee members must sign (**Signatures needed**, 1 to 3, normally 2).',
                '**Login security**: how many wrong passwords are allowed and for how many minutes the account is locked.',
                '**Maintenance mode**: when it is on, only administrators can log in and everybody else is logged out. Write a **Message** for them.',
                '**Email (SMTP)**: the mail server (host, port, security), the username and password, the sender email and name. The password is saved encrypted. Press [Send test email] (2) to check the setup: it sends one mail with the **saved** settings, even while the switch is off.',
                'The switch **Send emails** (1) is **off** at first, and nothing is sent while it is off. Switch it on when the test mail arrived. Then every new teacher, student or admin gets their login details by email.',
                'Press [Save Settings] (3) after a change.',
            ],
            'image' => 'settings.jpg',
            'link' => ['Open Settings', 'admin/settings/index.php'],
        ],

        [
            'id' => 'sessions', 'icon' => 'calendar3', 'title' => 'Academic Sessions',
            'summary' => 'A session is a period such as "Spring 2026". Exactly one session is **current**. A session that is used by a batch cannot be deleted.',
            'steps' => [
                'Open **University > Academic Sessions** and press [Add Session] (1).',
                'Write the **Name** and the **Year**, tick **Current session** if it is the running one, choose the status and save.',
                'Use the pencil to edit and the bin to delete.',
            ],
            'image' => 'sessions.jpg',
            'link' => ['Open Academic Sessions', 'admin/university/sessions/index.php'],
        ],

        [
            'id' => 'batches', 'icon' => 'collection', 'title' => 'Batches',
            'summary' => 'A batch is a shift plus an admission session, for example "65B Evening". Students belong to a batch.',
            'steps' => [
                'Press [Add Batch] (1) and fill **Name**, **Code**, **Department**, **Shift** and **Admission session**.',
                'You can also press [Import CSV] (2) to add many batches at once (see the CSV section).',
                'A batch with students cannot be deleted.',
            ],
            'image' => 'batches.jpg',
            'link' => ['Open Batches', 'admin/university/batches/index.php'],
        ],

        [
            'id' => 'departments', 'icon' => 'building', 'title' => 'Departments',
            'summary' => 'This page lists the department(s). It is **read only**: the system is set for one department. A department that has batches, teachers or students cannot be deleted.',
            'image' => 'departments.jpg',
            'link' => ['Open Departments', 'admin/university/departments/index.php'],
        ],

        [
            'id' => 'teachers', 'icon' => 'person-workspace', 'title' => 'Teachers',
            'summary' => 'Teachers supervise groups, and some of them sit in a thesis committee.',
            'steps' => [
                'Press [Add Teacher] (2): name, email, phone, employee ID, department, designation and status. There is no password box: the teacher gets the default password **wub@teacher123** and must change it at the first login.',
                'The list has a **Thesis Committee** column (Yes/No) that shows who is in a committee.',
                'Use the pencil to edit, the bin to delete. Set the status to inactive instead of deleting when a teacher leaves.',
                'Press [Import CSV] (1) to add many teachers at once.',
            ],
            'image' => 'teachers.jpg',
            'link' => ['Open Teachers', 'admin/university/teachers/index.php'],
        ],

        [
            'id' => 'students', 'icon' => 'mortarboard', 'title' => 'Students',
            'summary' => 'Students need a department and a batch. The batch must belong to the chosen department.',
            'steps' => [
                'Press [Add Student] (2) and fill name, email, student ID, department, batch and the optional details. The student gets the default password **wub@student123** and must change it at the first login.',
                'Search by name or ID, filter by batch and status.',
                'Press [Import CSV] (1) to add a whole batch at once.',
            ],
            'image' => 'students.jpg',
            'link' => ['Open Students', 'admin/university/students/index.php'],
        ],

        [
            'id' => 'import', 'icon' => 'file-earmark-spreadsheet', 'title' => 'Import teachers, students or batches from a CSV file',
            'summary' => 'The same box exists on the Teachers, Students and Batches pages. Use names, not numbers: the department code, the batch code and the session name.',
            'steps' => [
                'Press [Import CSV]. The import box opens.',
                '**Step 1** - pick the values (for example the batch) the file is for.',
                '**Step 2** - press **Download sample CSV**, open it in Excel and fill your rows. Save as CSV (comma delimited).',
                '**Step 3** - choose the file and press [Upload].',
                'There is no password column: every imported user gets the default password of the role. No emails are sent for an import (tell the users, or use [Reset to default password] later to send one).',
                'Up to 500 rows and 1 MB. Good rows are saved. Bad rows are listed with the **line number** and the reason, so you can fix only those and upload again.',
            ],
            'image' => 'import.jpg',
        ],

        [
            'id' => 'teams', 'icon' => 'people', 'title' => 'Groups/Teams',
            'summary' => 'A group/team has 2 to 3 students of one batch. **You approve** new groups and member changes (the committee does not).',
            'steps' => [
                'Press [Add Group/Team] (1) to make a group yourself. It is approved at once.',
                'New requests from students wait on top of the page. Press [Approve] or [Reject] (write a reason). The students get a notification.',
                'A group may ask for a member change only once. Approve or reject it the same way. You can also edit the members directly.',
                'A group can be deleted only while no research uses it.',
            ],
            'image' => 'teams.jpg',
            'link' => ['Open Groups/Teams', 'admin/university/teams/index.php'],
        ],

        [
            'id' => 'committees', 'icon' => 'people-fill', 'title' => 'Thesis Committees',
            'summary' => 'The committee decides on topics and finishes. You choose who sits in it and who signs. **You cannot sign yourself.**',
            'steps' => [
                'Open **University > Thesis Committees** and press [Add Committee] (1).',
                'Write the **Name** and choose **Works for**: the whole university or one department.',
                'Press **Add a teacher** (2), search and pick each member (a committee can have 7 or 8 members).',
                'Tick **Can approve** (1 in the next picture) for the members who sign. Add the **Chair** if you wish. Set the status to Active and save.',
                'Any 2 of the ticked members decide a topic or a finish (the number is in Settings).',
            ],
            'tips' => [
                'The members who are not ticked can still look at topics and assign supervisors, but cannot sign.',
                'An inactive committee gives nobody any right.',
            ],
            'image' => ['committees.jpg', 'committee_form.jpg'],
            'link' => ['Open Thesis Committees', 'admin/university/committees/index.php'],
        ],

        [
            'id' => 'library', 'icon' => 'journal-bookmark', 'title' => 'Research Library and Add Thesis / Research',
            'summary' => 'The library lists every research with its status. You add **finished** research (the archive) directly, with no approval. New work starts with a group and a supervisor.',
            'steps' => [
                'Open **Research Library**. Search, filter by type, category, batch and year, and sort.',
                'Press [Add Research] (1) to add an archive research.',
                'Fill **Title**, **Abstract**, **Type**, **Category**, **Year**, **Status** (choose Completed for the archive), the optional **Supervisor**, the **Tags**, and the **Group/Team** (needed for an ongoing research).',
                'Press [Add Research] to save. Use **Edit** and **Delete** in the list. **Activity** shows the history, **Full detail** the whole page.',
            ],
            'tips' => ['Delete moves a research to the Trash. Nothing is lost until you delete it for ever there.'],
            'image' => ['library.jpg', 'add.jpg', 'view.jpg'],
            'link' => ['Open the Research Library', 'admin/research/index.php'],
        ],

        [
            'id' => 'assign', 'icon' => 'person-check', 'title' => 'Assign Supervisor',
            'summary' => 'Give an approved group its supervisor. The group can start working at once and sends its topic later.',
            'steps' => [
                'Open **Research Library > Assign Supervisor**. Use the search and the batch filter.',
                'Press **Choose a supervisor** (1) in the row of the group and pick the teacher.',
                'Press [Assign] (2). The group and the teacher are told.',
            ],
            'tips' => ['A committee member can do the same from the teacher panel.'],
            'image' => 'assign.jpg',
            'link' => ['Open Assign Supervisor', 'admin/research/assign.php'],
        ],

        [
            'id' => 'waiting', 'icon' => 'hourglass-split', 'title' => 'Pending Submissions, Ongoing Research and Completion Requests',
            'summary' => 'These three pages show the research on its way. For you they are **read only**: the decision belongs to the thesis committee. You see the signatures and who still has to sign.',
            'steps' => [
                '**Pending Submissions**: topics that wait for the committee ("Waiting for the committee signatures").',
                '**Ongoing Research**: research that is being worked on.',
                '**Completion Requests**: finished work that waits for the committee. Press **Project work** to read the project.',
            ],
            'tips' => ['When a topic waits too long, ask the committee members to sign. You can also check that **Signatures needed** is not larger than the ticked members.'],
            'image' => ['pending.jpg', 'ongoing.jpg', 'completion.jpg'],
        ],

        [
            'id' => 'categories', 'icon' => 'tags', 'title' => 'Research Categories and Tags',
            'summary' => 'Categories are the subject areas (one for each research). Tags are small keywords (many for each research). Students choose from them when they send a topic, and use them to search the library.',
            'steps' => [
                'Press [Add Category] (1) or [Add Tag] (1): write the **Name**, a **Description** and the **Status**.',
                'Use the pencil and the bin to edit or delete. Switch the status to inactive to hide an item without deleting it.',
            ],
            'image' => ['categories.jpg', 'tags.jpg'],
        ],

        [
            'id' => 'trash', 'icon' => 'trash', 'title' => 'Trash',
            'summary' => 'A deleted research waits here. Restore it, or delete it **for ever** with everything that belongs to it (history, comments, milestones). Deleting for ever cannot be undone.',
            'image' => 'trash.jpg',
            'link' => ['Open the Trash', 'admin/research/trash.php'],
        ],

        [
            'id' => 'evaluation', 'icon' => 'clipboard-check', 'title' => 'Evaluation',
            'summary' => 'A read-only view of every project: press [Open] to see its Evaluations, Progress updates, Milestones and Feedback in tabs. Use the type buttons (All, Project, Thesis, Research Paper) to filter.',
            'image' => 'evaluation.jpg',
            'link' => ['Open Evaluation', 'admin/evaluation/index.php'],
        ],

        [
            'id' => 'admins', 'icon' => 'shield-lock', 'title' => 'Admins',
            'summary' => 'More than one administrator can exist. Press [Add Admin] (1), write the name and email: the new administrator gets the default password **wub@admin123** and must change it at the first login. Use **Edit Profile** to change one. You cannot delete or deactivate yourself, and the last active administrator always stays.',
            'image' => 'admins.jpg',
            'link' => ['Open Admins', 'admin/admins/index.php'],
        ],

        [
            'id' => 'reports', 'icon' => 'graph-up', 'title' => 'Reports',
            'summary' => 'Five reports with filters: **Research**, **Groups/Teams**, **Students**, **Supervisors** and **Evaluations**. The cards on top give the totals.',
            'steps' => [
                'Choose a report, then filter by kind, status, category, batch or year.',
                'Press [Excel] (1) for an .xlsx file, [CSV] (2) for a CSV file, or [Print / PDF] (3) to print every row (landscape) or save a PDF.',
            ],
            'image' => 'reports.jpg',
            'link' => ['Open Reports', 'admin/reports/index.php'],
        ],

        [
            'id' => 'notifications', 'icon' => 'bell', 'title' => 'Notifications',
            'summary' => 'The bell shows what needs you: new group requests, member changes, and (only when no committee exists) topics and finishes. Use **All** or **Unread** and [Mark all as read].',
            'image' => 'notifications.jpg',
            'link' => ['Open all notifications', 'admin/notifications.php'],
        ],

        [
            'id' => 'routine', 'icon' => 'calendar-check', 'title' => 'Your daily routine and common questions',
            'summary' => 'A short checklist.',
            'steps' => [
                '**Every day:** approve or reject new group requests and member changes (Groups/Teams).',
                '**Every day:** assign supervisors to the groups that have none.',
                '**Every term:** add the session and the new batch, import the new students, check the committee members.',
                '**A student forgot the password:** open **Students**, edit the student and press [Reset to default password]. The student gets **wub@student123** again (and an email when sending is on) and must choose a new password at the next login.',
                '**A topic is stuck:** it waits for the committee. Check the signatures on **Pending Submissions** and remind the members.',
                '**The system must stop for some time:** turn on **Maintenance mode** in Settings.',
            ],
        ],

    ],
];
