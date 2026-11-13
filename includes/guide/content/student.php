<?php
/*
 * The student guide. [Button name] is drawn like a button, **bold** is bold.
 * The pictures are in assets/admin_assets/images/guide/student/ (the red numbers in a picture are the numbers in the steps).
 */

return [
    'title'  => 'Student guide',
    'folder' => 'student',
    'intro'  => 'Everything a student does in UPTMS, from the first login to the finished thesis or project. Follow the sections in order. The red numbers in the pictures show the buttons to press.',
    'sections' => [

        [
            'id' => 'login', 'icon' => 'box-arrow-in-right', 'title' => 'Sign in and change your password',
            'summary' => 'The administrator makes your account. Your first password is the default password **wub@student123** (it can also arrive by email). One login page is used by everybody: the system knows from your account that you are a student.',
            'steps' => [
                'Open the UPTMS address in your browser.',
                'Type your **Email Address** (1) and your **Password** (2).',
                'Press [Sign In to Dashboard] (3). The student dashboard opens.',
                'The first time, the system opens **My Profile** by itself and locks the other pages. Write the default password as the **current password**, choose your own new password (at least 8 characters, not the default) and press [Update Password]. Then everything opens.',
            ],
            'tips' => [
                'After a few wrong passwords the account is locked for some minutes. Wait, or ask the administrator.',
                'You cannot change your email yourself. Ask the administrator if it is wrong.',
                'When the system is in maintenance mode only the administrator can log in. Try again later.',
            ],
            'image' => ['login.jpg', 'forced_password.jpg'],
            'link' => ['Open My Profile', 'student/profile.php'],
        ],

        [
            'id' => 'dashboard', 'icon' => 'speedometer2', 'title' => 'The dashboard and the menu',
            'summary' => 'The dashboard is your home page. It shows how many research the library holds, how many are ongoing, your own research requests and your projects, and a **What is new** box when something happened (a comment, an evaluation, an approval).',
            'steps' => [
                'The **left menu** has four parts: Overview (Dashboard), Research (Research Library), Project Work (My Group/Team, My Research, Milestones, Progress Updates, Feedback, Evaluations) and Account (My Profile, Logout).',
                'The **bell** at the top shows your notifications. A red number means something new.',
                'The **Search Research** box on the dashboard is a quick search of the library.',
            ],
            'image' => 'dashboard_work.jpg',
            'link' => ['Open the dashboard', 'student/index.php'],
        ],

        [
            'id' => 'library', 'icon' => 'journal-bookmark', 'title' => 'Search the Research Library before you choose a topic',
            'summary' => 'The library is the memory of the department: every finished project, thesis and research paper, and (if you tick the box) the research that is going on now. Use it first, so you know **how much was already done** on your idea.',
            'steps' => [
                'Open **Research Library** in the left menu.',
                'Type a word of your topic in the search box (1), for example "attendance" or "bangla".',
                'Read the line **About N results**: it tells you how many research match.',
                'Narrow the list with the type buttons (All, Project, Thesis, Research Paper), the category, the tags and the year. Tick or untick [Include ongoing] (3) to see what other groups are doing now.',
                'Click a title (2) to open the full details: the abstract, the category, the year and the supervisor.',
            ],
            'tips' => [
                'Few or no results means the topic is new. Many results mean you should choose a different angle.',
                'Only completed research (and ongoing research when ticked) is shown. Topics that wait for approval are private.',
            ],
            'image' => 'library.jpg',
            'link' => ['Open the Research Library', 'student/research/index.php'],
        ],

        [
            'id' => 'details', 'icon' => 'file-earmark-text', 'title' => 'Read the details of a research',
            'summary' => 'A click on a title in the library opens the details page. Use it to learn what a past group did.',
            'steps' => [
                'Read the **abstract** and the tags to understand the work.',
                'Check the **category**, the **year** and the **supervisor**: a supervisor who knows your subject is a good person to ask.',
                'Press **Back to library** to return to your search.',
            ],
            'image' => 'library_details.jpg',
        ],

        [
            'id' => 'group', 'icon' => 'people', 'title' => 'Make your group/team',
            'summary' => 'A research is done by a group/team of **2 or 3 students** of the same batch. You need an approved group before you can send a topic. The administrator can also make the group for you.',
            'steps' => [
                'Open **My Group/Team** in the left menu. If you have no group, the page says "You are not in a group/team".',
                'Press [Request a group/team] (1).',
                'Type the **Group/Team name** and choose your team mates in the list (you are always a member). Search by name or student ID. Students who are already in a group cannot be chosen.',
                'Press [Send request]. The status is now Requested.',
                'The administrator approves it (status **Approved**) or rejects it with a reason. You get a notification either way. After a rejection you can send a new request.',
            ],
            'tips' => [
                'The size of a group is set by the administrator (normally 2 to 3 students).',
                'After approval you cannot change the members yourself. Use [Request a member change] once. The administrator decides. A group can ask only once.',
                'A student can be in one group. When your research is completed, the group is closed.',
            ],
            'image' => 'team_request.jpg',
            'link' => ['Open My Group/Team', 'student/team.php'],
        ],

        [
            'id' => 'group-page', 'icon' => 'person-check', 'title' => 'Your approved group',
            'summary' => 'This is how My Group/Team looks after the approval: the group name, the batch, its status, the project status and the members.',
            'steps' => [
                'The **Group/Team status** must be Approved.',
                '**Project status** tells whether the group has a research yet. If not, the next step is the supervisor and the topic (next section).',
            ],
            'image' => 'team.jpg',
        ],

        [
            'id' => 'topic', 'icon' => 'send', 'title' => 'Get a supervisor and send your topic',
            'summary' => 'There are two ways. In both of them the **thesis committee** decides on the topic. Your supervisor is chosen by the administrator or the committee.',
            'steps' => [
                '**Way 1 - the supervisor comes first.** The administrator or the committee gives your group a supervisor. In **My Research** your group has a row with the status **Supervisor assigned** and the text "Your topic is not sent yet". You can start working with your supervisor at once. When the topic is ready, press [Send topic] (1).',
                '**Way 2 - the topic comes first.** In **My Research** press [Request a Topic] (2) and fill the same form. The committee approves it, then a supervisor is assigned.',
                'Fill the form (see the next section): the title, the abstract, the type, the category and the tags.',
                'Press [Save changes] (when you send the topic of an assigned group) or [Send request] (a new request). The status becomes **Requested**.',
            ],
            'tips' => [
                'First search the library. A clear title and a short, honest abstract are approved faster.',
                'You can edit or withdraw a topic only until the committee decided on it.',
            ],
            'image' => 'my_research_assigned.jpg',
            'link' => ['Open My Research', 'student/research/my_research.php'],
        ],

        [
            'id' => 'topic-form', 'icon' => 'pencil-square', 'title' => 'The topic form',
            'summary' => 'The form with every field filled in. All fields with a red star are required.',
            'steps' => [
                '**Title** - the name of your work.',
                '**Abstract** - what you will do and why, in a few sentences.',
                '**Type** - Project, Thesis or Research Paper. **Category** - the subject area. **Tags** - small keywords that help others find your work in the library.',
            ],
            'image' => 'topic_form.jpg',
        ],

        [
            'id' => 'approval', 'icon' => 'patch-check', 'title' => 'The committee decides on your topic',
            'summary' => 'Your topic goes to the thesis committee. The members who are allowed to approve sign it. **Two signatures decide.** You see the signatures on your project page and in My Research.',
            'steps' => [
                'While the topic waits, the page shows **0 of 2 signatures** and who has signed. You can keep working with your supervisor.',
                '**Approved** (2 members approve): the research becomes **Ongoing** and your project page opens fully.',
                '**Needs correction** (one member asks for a change): read the note, press edit, change the topic and save. The signatures start again.',
                '**Rejected** (2 members reject): read the reason in the history. If your group still has its supervisor, the group sends a new topic.',
            ],
            'tips' => ['You get a notification for every decision. Open the bell to read it.'],
            'image' => 'waiting.jpg',
        ],

        [
            'id' => 'project', 'icon' => 'folder2-open', 'title' => 'Work on your project: milestones, tasks and comments',
            'summary' => 'Once the work can start (your group has a supervisor), **My Research** opens the **Project** page. It is the place where the group and the supervisor work together.',
            'steps' => [
                'Press [Add milestone] (1) to add a big step with a **Title**, a short **Description** and a **Due date**, then [Save milestone].',
                'Under a milestone write a small job in the **Add a task...** box and press [Add]. Tick the box of a task when it is done. The **progress bar** moves by itself.',
                'Use **Comments** (2) to write to your supervisor: write the text and press [Post comment]. Under a milestone use **Feedback** for talks about that step.',
                'The **Evaluations** box shows the rating (1 to 5) and the remark your supervisor wrote.',
                'Any member of the group can add and tick tasks. Everything is written in the history, with the name of the person.',
            ],
            'tips' => ['A completed research is read only: nothing can be changed any more.'],
            'image' => 'workspace.jpg',
            'link' => ['Open My Research', 'student/research/my_research.php'],
        ],

        [
            'id' => 'lists', 'icon' => 'list-check', 'title' => 'Milestones, Progress Updates, Feedback and Evaluations',
            'summary' => 'These four menu items show the same project from four sides. Each one first lists your projects. Press [Open] on a project and use the tabs on top to jump between them.',
            'steps' => [
                '**Milestones** (2): the steps with their due date and progress.',
                '**Progress Updates**: a timeline of everything that happened (tasks done, comments, evaluations).',
                '**Feedback**: the comments written under milestones, yours and your supervisor\'s.',
                '**Evaluations**: the ratings and remarks of your supervisor.',
                'Press [Open the project] to go back to the work page.',
            ],
            'image' => ['milestones_open.jpg', 'progress.jpg'],
            'link' => ['Open Milestones', 'student/milestones.php'],
        ],

        [
            'id' => 'feedback', 'icon' => 'chat-left-text', 'title' => 'Feedback and evaluations from your supervisor',
            'summary' => 'Read the answers of your supervisor here. After a meeting your supervisor also evaluates the progress with a **rating of 1 to 5** and a remark.',
            'steps' => [
                '**Feedback**: every comment with the milestone it belongs to. Reply on the project page under the milestone.',
                '**Evaluations**: the rating (for example 4 of 5 - Very good), the remark, who wrote it and the date.',
            ],
            'image' => ['feedback.jpg', 'evaluations.jpg'],
            'link' => ['Open Evaluations', 'student/evaluations.php'],
        ],

        [
            'id' => 'finish', 'icon' => 'flag', 'title' => 'Finish the research',
            'summary' => 'When the work is done, the research is closed in four steps. Everybody must agree: first your supervisor, then every team member, then the thesis committee.',
            'steps' => [
                'On the **Project** page, in the box **Finishing the research**, a member of the group presses [Request to finish] (1).',
                'The **supervisor** approves or rejects (with a reason).',
                'After the supervisor approved, **every team member** presses [I confirm the work is finished] (see the next section).',
                'Then **2 members of the thesis committee** approve. The research becomes **Completed**, the group is closed, and your work appears in the Research Library for the next students.',
            ],
            'tips' => [
                'The student who asked can take the request back with **Cancel the request** until the committee has it.',
                'If the supervisor or the committee rejects, the reason is shown. Fix the problem and ask again.',
            ],
            'image' => 'finish_waiting.jpg',
        ],

        [
            'id' => 'finish-confirm', 'icon' => 'check2-circle', 'title' => 'Confirm the finish as a team member',
            'summary' => 'This is what every other member of the group sees after the supervisor approved. Check the work, then confirm. The request goes to the committee only after all members confirmed.',
            'image' => 'finish_confirm.jpg',
        ],

        [
            'id' => 'notifications', 'icon' => 'bell', 'title' => 'Notifications',
            'summary' => 'The bell at the top shows what happened: an evaluation, a comment, a decision of the committee, an approval of your group. Press it to read.',
            'steps' => [
                'Use **All** or **Unread** (small tabs) to filter.',
                'Press [Mark all as read] when you have read everything. Press **See all notifications** for the full list.',
            ],
            'image' => 'notifications.jpg',
            'link' => ['Open all notifications', 'student/notifications.php'],
        ],

        [
            'id' => 'profile', 'icon' => 'person', 'title' => 'Your profile and password',
            'summary' => 'Keep your details correct: they are printed in the reports.',
            'steps' => [
                'Open **My Profile**. Press the small camera on your picture to add a photo (JPG, PNG or WebP, up to 2 MB).',
                'Change your name or phone and press [Update Profile] (1).',
                'To change the password, write the current password and the new one twice, and press [Update Password] (2).',
            ],
            'image' => 'profile.jpg',
            'link' => ['Open My Profile', 'student/profile.php'],
        ],

        [
            'id' => 'questions', 'icon' => 'question-circle', 'title' => 'Common questions',
            'summary' => 'Quick answers.',
            'steps' => [
                '**I cannot send a topic.** Your group must be Approved first. See "Make your group/team".',
                '**I cannot join a group.** A student can be in one group, or the group is full. Ask the administrator.',
                '**I forgot my password.** Ask the administrator to reset it.',
                '**My project page does not open.** A group needs a supervisor (or an approved topic) first.',
                '**Who approves what?** The administrator approves groups. The thesis committee approves topics and the finish. The supervisor guides, comments and evaluates.',
            ],
        ],

    ],
];
