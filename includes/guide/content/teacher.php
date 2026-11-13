<?php
/*
 * The teacher (supervisor) guide. Pictures: assets/admin_assets/images/guide/teacher/.
 * A teacher who is in a thesis committee also gets the committee guide (includes/guide/content/committee.php).
 */

return [
    'title'  => 'Teacher guide',
    'folder' => 'teacher',
    'intro'  => 'How a teacher supervises the groups of UPTMS: follow the work, comment, evaluate and decide when a group may finish. If you are also a member of a thesis committee, open the Committee guide with the button above.',
    'sections' => [

        [
            'id' => 'login', 'icon' => 'box-arrow-in-right', 'title' => 'Sign in and change your password',
            'summary' => 'The administrator makes your account. Your first password is the default password **wub@teacher123** (it can also arrive by email). The same login page is used by every role.',
            'steps' => [
                'Open the UPTMS address, type your **Email Address** and **Password** and press [Sign In to Dashboard].',
                'The first time, the system opens **My Profile** by itself and locks the other pages until you choose your own password: write the default as the current password, then your new one, and press [Update Password].',
                'Add your photo, name and phone there too and press [Update Profile].',
            ],
            'tips' => ['Ask the administrator when your email is wrong or you forgot the password.'],
            'image' => 'profile.jpg',
            'link' => ['Open My Profile', 'teacher/profile.php'],
        ],

        [
            'id' => 'dashboard', 'icon' => 'speedometer2', 'title' => 'The dashboard',
            'summary' => 'The dashboard shows your ongoing projects, the open tasks of your groups, the projects you have not evaluated yet and the completed projects. The box **What is new** tells you what happened (a group posted a comment, a topic was approved, a finish was requested).',
            'steps' => [
                'The **left menu** has: Overview (Dashboard), Supervision (My Research, Milestones, Progress Updates, Feedback, Evaluations), Research (Research Library) and Account. Committee members also see **Thesis Committee**.',
                'In **My Projects** press [Open] on a row to go straight to the project.',
                'The **bell** shows your notifications.',
            ],
            'image' => 'dashboard.jpg',
            'link' => ['Open the dashboard', 'teacher/index.php'],
        ],

        [
            'id' => 'my-research', 'icon' => 'folder2-open', 'title' => 'My Research: the groups you supervise',
            'summary' => 'A group becomes your project when you are assigned as its supervisor (by the administrator or the thesis committee). You do not submit research yourself.',
            'steps' => [
                'Open **My Research**. The cards count the research that waits for the committee, the ongoing ones you supervise, the completed ones and the rejected ones.',
                'Each row shows the topic, the group, the batch, the category, the status and the progress.',
                'You are told by a notification when a group is given to you. A group may start to work at once, even before it has sent its topic ("Supervisor assigned").',
                'Press the eye or the title to open the project.',
            ],
            'image' => 'my_research.jpg',
            'link' => ['Open My Research', 'teacher/research/my_research.php'],
        ],

        [
            'id' => 'project', 'icon' => 'kanban', 'title' => 'The project page: milestones, comments and evaluation',
            'summary' => 'The project page is where you guide the group. You see the group, the topic, the progress bar, the milestones with their tasks, the comments and the evaluations.',
            'steps' => [
                'Press [Add milestone] to plan a step for the group (title, description, due date). The group adds its own tasks under it.',
                'Write in the **Comments** box and press [Post comment] to talk to the group. Under a milestone use **Feedback** to answer about that step.',
                'To evaluate, choose a **Rating** (1 to 5), write the **Report on the progress** and press [Save evaluation]. You can evaluate again after the next meeting.',
                'Only you and the group can change the project. The administrator and the committee can only read it.',
            ],
            'tips' => [
                'A good habit: one milestone for each month, and one evaluation after every meeting.',
                'You can delete only your own comments.',
            ],
            'image' => 'workspace.jpg',
        ],

        [
            'id' => 'lists', 'icon' => 'list-check', 'title' => 'Milestones, Progress Updates, Feedback and Evaluations',
            'summary' => 'These four menu items show all your projects from four sides. Each page first lists your projects. Press [Open] on one, then use the tabs on top (Evaluations, Progress updates, Milestones, Feedback) to move between the views.',
            'steps' => [
                '**Milestones**: the steps of the group with the due date and the progress.',
                '**Progress Updates**: a timeline of every task, comment and evaluation.',
                '**Feedback**: the comments under milestones.',
                '**Evaluations**: your ratings and remarks. The projects you have not evaluated yet are marked on the dashboard.',
            ],
            'image' => ['milestones.jpg', 'progress.jpg'],
            'link' => ['Open Evaluations', 'teacher/evaluations.php'],
        ],

        [
            'id' => 'finish', 'icon' => 'flag', 'title' => 'Decide when a group may finish',
            'summary' => 'When the work is done, a student asks to finish. You are the **first** person who decides. Then the team confirms and the thesis committee signs.',
            'steps' => [
                'You get a notification and the project page shows the box **Finishing the research** with the steps.',
                'Check the work with the group.',
                'Press [Approve] (1) when the work is complete. Press [Reject] (2) when it is not. For a rejection you **must write the reason** in the note box.',
                'After your approval every team member confirms, then 2 members of the committee approve and the research becomes **Completed**. It then appears in the Research Library.',
            ],
            'image' => 'finish.jpg',
        ],

        [
            'id' => 'library', 'icon' => 'journal-bookmark', 'title' => 'The Research Library',
            'summary' => 'The library lists the completed research of the department (and the ongoing research when you tick **Include ongoing**). Use it to help a group choose a topic that was not done before.',
            'steps' => [
                'Search by words, choose the type (Project, Thesis, Research Paper), category, tags, batch or year.',
                'Open a row to see the abstract, the group and the supervisor.',
            ],
            'image' => 'library.jpg',
            'link' => ['Open the Research Library', 'teacher/research/index.php'],
        ],

        [
            'id' => 'notifications', 'icon' => 'bell', 'title' => 'Notifications',
            'summary' => 'The bell at the top tells you when a group is given to you, when a student comments, when a topic is approved and when a finish is requested.',
            'steps' => [
                'Use **All** or **Unread**, [Mark all as read], or open **See all notifications** for the full list.',
            ],
            'link' => ['Open all notifications', 'teacher/notifications.php'],
        ],

        [
            'id' => 'committee', 'icon' => 'people-fill', 'title' => 'If you are a member of a thesis committee',
            'summary' => 'When the administrator adds you to a thesis committee, a new part called **Thesis Committee** appears in your menu (Topic Requests, Assign Supervisor, Completion Requests). Open the **Committee guide** (button at the top of this page) to learn how to sign a topic and a finish.',
            'link' => ['Open the Committee guide', 'teacher/committee/guide.php'],
        ],

        [
            'id' => 'questions', 'icon' => 'question-circle', 'title' => 'Common questions',
            'summary' => 'Quick answers.',
            'steps' => [
                '**I do not see my group.** You must be assigned as its supervisor by the administrator or the committee.',
                '**Can I approve a topic?** Only the committee members who are allowed to approve can. A supervisor who is one of them signs like the others.',
                '**Can I change a student\'s task?** No. Comment, and the group changes it.',
                '**The group says it cannot finish.** The research must be Ongoing (the topic approved). Check the status in My Research.',
            ],
        ],

    ],
];
