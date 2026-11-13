<?php
/*
 * The thesis committee guide: only for the teachers who are in an active committee.
 * Pictures: assets/admin_assets/images/guide/committee/.
 */

return [
    'title'  => 'Thesis committee guide',
    'folder' => 'committee',
    'intro'  => 'What a member of a thesis committee does in UPTMS: sign the topics the groups send, assign supervisors and sign the requests to finish a research. The administrator cannot do these things, only the committee can.',
    'sections' => [

        [
            'id' => 'rules', 'icon' => 'shield-check', 'title' => 'The rules in one minute',
            'summary' => 'The administrator makes the committee and ticks **Can approve** for the members who sign. Everything else follows from that.',
            'steps' => [
                'A committee works for the whole university or for one department. You only see the research and groups of your departments.',
                'Only the members who are ticked **Can approve** sign. The other members can look at everything and assign supervisors, but they cannot sign.',
                '**Any 2 signatures decide** (the administrator can change this number in Settings).',
                'One **Correction** sends a topic back at once. **Approve** and **Reject** need 2 signatures.',
                'You can change your own signature until the topic is decided. Every signature is written in the history with your name.',
            ],
            'tips' => ['If a button says "You are not one of the members who approve", ask the administrator to tick **Can approve** for you.'],
        ],

        [
            'id' => 'menu', 'icon' => 'layout-sidebar', 'title' => 'Where to find the committee pages',
            'summary' => 'In your teacher menu a part called **Thesis Committee** appears with three pages. A small number shows how many wait for you.',
            'steps' => [
                '**Topic Requests**: the topics the groups sent.',
                '**Assign Supervisor**: groups that have no supervisor yet.',
                '**Completion Requests**: the research the groups finished.',
            ],
            'link' => ['Open Topic Requests', 'teacher/committee/topics.php'],
        ],

        [
            'id' => 'topics', 'icon' => 'clipboard2-check', 'title' => 'Sign a topic',
            'summary' => 'Each row is a topic that waits. You see the title, who sent it, the signatures (**0 of 2 signatures**, with the ticked members and whether they signed) and the buttons.',
            'steps' => [
                'Use the search, the category, batch, year and sort to find a topic. Open **View** to read the abstract and the history.',
                'Press [Approve] (1) when the topic is good.',
                'Press [Correction] (2) to send it back to the group. **Write what must change** in the note.',
                'Press [Reject] (3) when the topic cannot be accepted. **Write the reason**; it needs 2 rejections.',
                'When 2 members approved, the research becomes **Ongoing** (or **Approved** when no supervisor is set yet) and the group and the supervisor are told.',
            ],
            'tips' => [
                'Before you decide, search the Research Library: has the same topic been done?',
                'A rejected topic goes back to the group. The group keeps its supervisor and can send a new topic.',
            ],
            'image' => ['topics.jpg', 'topics_signed.jpg'],
        ],

        [
            'id' => 'details', 'icon' => 'file-earmark-text', 'title' => 'Read the details first',
            'summary' => 'The details page shows the abstract, the group, the supervisor, the signatures and the history (what happened, when and by whom).',
            'image' => 'topic_details.jpg',
        ],

        [
            'id' => 'assign', 'icon' => 'person-check', 'title' => 'Assign a supervisor to a group',
            'summary' => 'A group can get its supervisor **before** it has a topic. It then starts to work at once and sends its topic later.',
            'steps' => [
                'Open **Assign Supervisor**. The list shows the approved groups without a supervisor. Use the search, the department and the batch to find a group.',
                'Press **Choose a supervisor** (1) in the row, search the teacher and pick one.',
                'Press [Assign] (2). The group and the teacher get a notification. The group sees the status **Supervisor assigned**.',
                'To give a supervisor to a topic that is approved but has none, use the same page: the list of approved topics is below.',
            ],
            'tips' => ['The administrator can assign supervisors too. A committee member can be a supervisor.'],
            'image' => 'assign.jpg',
            'link' => ['Open Assign Supervisor', 'teacher/committee/assign.php'],
        ],

        [
            'id' => 'completion', 'icon' => 'flag', 'title' => 'Sign a request to finish',
            'summary' => 'A request arrives here only after the **supervisor approved** and **every team member confirmed**. You see the group, the supervisor, the work done and the signatures.',
            'steps' => [
                'Open **Completion Requests** and read the row. Press **Details** when you need more.',
                'Press [Sign: complete this research] (1) to approve.',
                'Press [Reject] (2) and **write the reason** to refuse. Two rejections close the request.',
                'When 2 members approved, the research becomes **Completed**. The group is closed and the work appears in the Research Library.',
            ],
            'image' => 'completion.jpg',
            'link' => ['Open Completion Requests', 'teacher/committee/completion.php'],
        ],

        [
            'id' => 'groups', 'icon' => 'people', 'title' => 'What the committee does not do',
            'summary' => 'The **administrator** approves new groups/teams and member changes, makes users, categories, tags, batches, reports and settings. The committee only decides about topics, supervisors and the finish.',
        ],

        [
            'id' => 'questions', 'icon' => 'question-circle', 'title' => 'Common questions',
            'summary' => 'Quick answers.',
            'steps' => [
                '**I see the topic but no buttons.** You are in the committee but not ticked **Can approve**. Ask the administrator.',
                '**Nothing waits for me.** The list is empty when no group sent a topic for your department.',
                '**I signed by mistake.** Open the topic again and press the right button: your signature is replaced until the topic is decided.',
                '**Two of us signed at the same moment.** The system saves both safely. The topic is decided by the second signature.',
            ],
        ],

    ],
];
