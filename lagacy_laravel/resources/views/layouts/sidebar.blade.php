@if (Auth::check())

    <div style="border-top: 2px solid white; margin: 15px 0;"></div>
    {{-- LPI User --}}
    @if (Auth::user()->type === 'LPI')
        <a href="{{ route('userDetails') }}" class="nav-link {{ request()->routeIs('userDetails') ? 'active' : '' }}">
            <i class="fa fa-chart-line me-2"></i>LPI Dashboard
        </a>


        <a href="{{ route('confcycles') }}"
            class="nav-link {{ request()->routeIs('confprojects') || request()->routeIs('confcycles') || request()->routeIs('newProject') || request()->routeIs('sessionStep2')|| request()->routeIs('sessionStep3')? 'active' : '' }}">
            <i class="fa fa-folder-open me-2"></i> Conf-Tool Projects
        </a>

        <a href="{{ route('cycles') }}"

            class="nav-link {{ request()->routeIs('uploadedOutcomes') || request()->routeIs('cycles') || request()->routeIs('projectOutcome1') || request()->routeIs('projectOutcome2') || request()->routeIs('project') || request()->routeIs('projectOutcomes') || request()->routeIs('upload') || request()->routeIs('projectDetails') ? 'active' : '' }}">
            <i class="fa fa-briefcase me-2"></i> Projects
        </a>
        <a href="{{ route('gradedcycles') }}"
            class="nav-link {{ request()->routeIs('gradedcycles') || request()->routeIs('gradedproject') ? 'active' : '' }}">
            <i class="fa fa-award me-2"></i> Graded Projects
        </a>
    @endif



    {{-- Reviewer --}}
    @if (Auth::user()->type === 'Reviewer')
        <a href="{{ route('reviewerDetails') }}"
            class="nav-link {{ request()->routeIs('reviewerDetails') ? 'active' : '' }}">
            <i class="fa fa-chart-line me-2"></i> Reviewer Dashboard
        </a>

        <a href="{{ route('cycles') }}"
            class="nav-link {{ request()->routeIs('cycles') || request()->routeIs('project') ? 'active' : '' }}">
            <i class="fa fa-briefcase me-2"></i> Review Projects
        </a>
    @endif

    {{-- Admin --}}
    @if (Auth::user()->type === 'Admin')
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa fa-dashboard me-2"></i> Admin Dashboard
        </a>

        {{--
        <a href="{{ route('AdminCycles') }}"
            class="nav-link {{ request()->routeIs('AdminCycles') || request()->routeIs('adminProgressSummary/*') ? 'active' : '' }}">
            <i class="fa fa-recycle me-2"></i> Cycles
        </a> --}}





        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#cycles">
                <i class="fas fa-recycle me-2"></i>Cycles
                <i class="fas fa-chevron-down float-end"></i>
            </a>

            <div class="collapse submenu {{ request()->routeIs('cycle', 'AdminCycles', 'conftoolProjects', 'newCycle', 'adminProgressSummary', 'confprojectadd') ? 'show' : '' }}"
                id="cycles">

                <a href="{{ route('cycle') }}"
                    class="nav-link {{ request()->routeIs('conftoolProjects') || request()->routeIs('newCycle') || request()->routeIs('cycle') || request()->routeIs('confprojectadd') ? 'active' : '' }}">
                    <i class="fa fa-recycle me-2"></i> All Cycles
                </a>


                <a href="{{ route('AdminCycles') }}"
                    class="nav-link {{ request()->routeIs('AdminCycles', 'adminProgressSummary') ? 'active' : '' }}">
                    <i class="fa fa-file-alt me-2"></i> Cycles Summary
                </a>

            </div>
        </li>



        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#projects">
                <i class="fas fa-briefcase me-2"></i>Projects
                <i class="fas fa-chevron-down float-end"></i>
            </a>

            <div class="collapse submenu {{ request()->routeIs(
                'grading',
                'projectDetails',
                'confprojects',
                'cycles',
                'assignReviewCycle',
                'AssignedReviewers',
                'gradedProjects',
                'newCycle',
                'cycle',
                'sessionStep2',
                'sessionStep3',
                'confcycles',
            ) ||
            request()->is('project/*') ||
            request()->is('assignReview/*') ||
            request()->is('AssignedReviewers') ||
            request()->is('gradedProjects') ||
            request()->is('newProject')
                ? 'show'
                : '' }}"
                id="projects">

                <a href="{{ route('confcycles') }}"
                    class="nav-link {{ request()->routeIs('confcycles') || request()->routeIs('sessionStep2') || request()->routeIs('sessionStep3') || request()->routeIs('confprojects') || request()->routeIs('newProject') ? 'active' : '' }}">
                    <i class="fa fa-briefcase me-2"></i> Conf-Tool Projects
                </a>

                {{-- <a href="{{ route('confprojects') }}"
                    class="nav-link {{ request()->routeIs('sessionStep2') || request()->routeIs('sessionStep3') || request()->routeIs('confprojects') || request()->routeIs('newProject') ? 'active' : '' }}">
                    <i class="fa fa-briefcase me-2"></i> Project Registration
                </a> --}}


                <a href="{{ route('cycles') }}"
                    class="nav-link {{ request()->routeIs('cycles') || request()->is('project/*') || request()->is('projectDetails/*') || request()->is('grading/*') ? 'active' : '' }}">
                    <i class="fa fa-briefcase me-2"></i> Projects
                </a>

                <a href="{{ route('assignReviewCycle') }}"
                    class="nav-link {{ request()->routeIs('assignReviewCycle') || request()->is('assignReview/*') ? 'active' : '' }}">
                    <i class="fa fa-pencil-square me-2"></i> Assign Reviewers
                </a>

                <a href="{{ route('AssignedReviewers') }}"
                    class="nav-link {{ request()->routeIs('AssignedReviewers') ? 'active' : '' }}">
                    <i class="fa fa-tags me-2"></i> Assigned Reviewers
                </a>


                <a href="{{ route('gradedProjects') }}"
                    class="nav-link {{ request()->routeIs('gradedProjects') ? 'active' : '' }}">
                    <i class="fa fa-award me-2"></i> Graded Projects
                </a>

                {{--
                <a href="{{ route('AdminCycles') }}"
                    class="nav-link {{ request()->routeIs('AdminCycles') ? 'active' : '' }}">
                    <i class="fa fa-file-text me-2"></i> Cycle Summary
                </a> --}}

            </div>
        </li>


        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#admintask">
                <i class="fas fa-clipboard-list me-2"></i>Admin Tasks
                <i class="fas fa-chevron-down float-end"></i>
            </a>



            <div class="collapse submenu {{ request()->routeIs(
                'user',
                'sendEmailAdmin',
                'EmailSendingStatus',
                'aboutUsSettings',
                'reviewerGrading',
                'uploadProgressAdmin',
                'reviewerAgrementAdmin',
                'announcementSetting',
                'announcementDetail',
                'file.explorer',
                'reviewerDetail',
                'userDetail',
                'newUser',
            ) ||
            request()->is('newAnnouncement') ||
            request()->is('announcementEdit/*')
                ? 'show'
                : '' }}"
                id="admintask">

                <a href="{{ route('user') }}"
                    class="nav-link {{ request()->routeIs('newUser', 'reviewerGrading') || request()->routeIs('user') || request()->routeIs('userDetail') || request()->routeIs('reviewerDetail') ? 'active' : '' }}">
                    <i class="fa fa-users me-2"></i> System Users
                </a>


                <a class="nav-link" data-bs-toggle="collapse" href="#emails">
                    <i class="fas fa-envelope me-2"></i>General Emails
                    <i class="fas fa-chevron-down float-end"></i>
                </a>

                <div class="collapse submenu {{ request()->routeIs('sendEmailAdmin') || request()->routeIs('EmailSendingStatus') ? 'show' : '' }}"
                    id="emails">

                    <a href="{{ route('sendEmailAdmin') }}"
                        class="nav-link {{ request()->routeIs('sendEmailAdmin') ? 'active' : '' }}">
                        <i class="fa fa-envelope me-2"></i> Send Emails
                    </a>

                    <a href="{{ route('EmailSendingStatus') }}"
                        class="nav-link {{ request()->routeIs('EmailSendingStatus') ? 'active' : '' }}">
                        <i class="fa fa-list me-2"></i> Email Logs
                    </a>

                </div>

                <a href="{{ route('announcementSetting') }}"
                    class="nav-link {{ request()->routeIs('announcementSetting') || request()->is('announcementEdit/*') || request()->is('newAnnouncement') || request()->is('announcementDetail/*') ? 'active' : '' }}">
                    <i class="fa fa-bullhorn me-2"></i> Announcements
                </a>

                <a href="{{ route('aboutUsSettings') }}"
                    class="nav-link {{ request()->routeIs('aboutUsSettings') ? 'active' : '' }}">
                    <i class="fa fa-users me-2"></i> Our Team
                </a>

                <a href="{{ route('file.explorer') }}"
                    class="nav-link {{ request()->routeIs('file.explorer') ? 'active' : '' }}">
                    <i class="fa fa-download me-2"></i> RTS Downloads
                </a>


                <a href="{{ route('uploadProgressAdmin') }}"
                    class="nav-link {{ request()->routeIs('uploadProgressAdmin') ? 'active' : '' }}">
                    <i class="fa fa-line-chart me-2"></i> Upload Reports
                </a>

                <a href="{{ route('pr2.extension') }}"
                    class="nav-link {{ request()->routeIs('pr2.extension') ? 'active' : '' }}">
                    <i class="fa fa-plus-circle me-2"></i> PR2 Extension
                </a>



                <a href="{{ route('reviewerAgrementAdmin') }}"
                    class="nav-link {{ request()->routeIs('reviewerAgrementAdmin') ? 'active' : '' }}">
                    <i class="fa fa-handshake me-2"></i> Reviewer Agreement
                </a>

            </div>
        </li>


        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#settings">
                <i class="fas fa-gear me-2"></i>Settings
                <i class="fas fa-chevron-down float-end"></i>
            </a>

            <div class="collapse submenu {{ request()->routeIs('emailNew', 'guageSetting', 'emailSetting', 'listFilesAndFolders', 'guageSetting', 'budgetAPIList') ? 'show' : '' }}"
                id="settings">

                <a href="{{ route('emailSetting') }}"
                    class="nav-link {{ request()->routeIs('emailSetting', 'emailNew') ? 'active' : '' }}">
                    <i class="fa fa-envelope me-2"></i> Email Templates
                </a>

                <a href="{{ route('listFilesAndFolders') }}"
                    class="nav-link {{ request()->routeIs('listFilesAndFolders') ? 'active' : '' }}">
                    <i class="fa fa-hdd me-2"></i> RTS Backups
                </a>


                <a href="{{ route('budgetAPIList') }}"
                    class="nav-link {{ request()->routeIs('budgetAPIList') ? 'active' : '' }}">
                    <i class="fas fa-dollar-sign me-2"></i> Budget API
                </a>


                <a href="{{ route('guageSetting') }}"
                    class="nav-link {{ request()->routeIs('guageSetting') ? 'active' : '' }}">
                    <i class="fas fa-dashboard me-2"></i> Guage Settings
                </a>

            </div>
        </li>
    @endif

@endif
