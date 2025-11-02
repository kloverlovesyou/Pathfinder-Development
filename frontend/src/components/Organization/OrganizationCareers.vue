<template>
  <div class="organization-careers">

    <!-- Hamburger Toggle -->
    <button class="hamburger" @click="toggleSidebar" :class="{ open: isSidebarOpen, shifted: isSidebarOpen }">
      <span></span>
      <span></span>
      <span></span>
    </button>

    <!-- Sidebar -->
    <transition name="slide">
      <aside class="sidebar" :class="{ collapsed: !isSidebarOpen }">

        <div class="space">

        </div>
        <!-- Avatar always visible -->
        <div class="avatar">
          <img :src="dictLogo" alt="DICT Logo" class="avatar-img" />
        </div>

        <!-- Profile Section (only when sidebar is open) -->
        <transition name="fade">
          <div v-if="isSidebarOpen" class="profile-section">
            <h3 class="org-name">{{ organizationName }}</h3>
            <div class="profile-actions">
              <div class="action" @click="navigateTo({ name: 'OrgProfile' })">
                <!-- Update Profile Icon -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M11.7278 8.27191C12.7534 9.87525 14.1247 11.2375 15.7464 12.2534L8.85673 19.144C8.43166 19.569 8.21841 19.7816 7.95731 19.9213C7.69637 20.0609 7.40171 20.1199 6.81278 20.2377L3.73563 20.853C3.40302 20.9195 3.23649 20.9525 3.14188 20.8578C3.04759 20.7632 3.08035 20.5971 3.14677 20.2651L3.76298 17.1879C3.88087 16.5985 3.93965 16.3035 4.07938 16.0424C4.21912 15.7814 4.43173 15.569 4.85673 15.144L11.7278 8.27191ZM16.1116 4.03656C16.6711 3.75929 17.3284 3.75931 17.888 4.03656C18.1821 4.18229 18.455 4.45518 19.0003 5.00043C19.5453 5.54545 19.8184 5.81774 19.9641 6.11175C20.2414 6.67123 20.2413 7.32861 19.9641 7.88812C19.8184 8.18221 19.5455 8.45517 19.0003 9.00043L17.2034 10.7963C15.5308 9.84498 14.1456 8.46859 13.1819 6.81781L15.0003 5.00043C15.5453 4.45539 15.8176 4.18234 16.1116 4.03656Z"
                    fill="#FFFDFD" />
                </svg>
                <span>Update Profile</span>
              </div>
            </div>
          </div>
        </transition>

        <div class="icon" @click="navigateTo('/organization')">
          <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M6.25 17.0585C6.25 16.0494 6.25 15.5448 6.47166 15.1141C6.69333 14.6833 7.1039 14.3901 7.92505 13.8035L13.8375 9.58034C14.3989 9.17938 14.6795 8.9789 15 8.9789C15.3205 8.9789 15.6011 9.17938 16.1625 9.58034L22.075 13.8035C22.8961 14.3901 23.3067 14.6833 23.5283 15.1141C23.75 15.5448 23.75 16.0494 23.75 17.0585V24.25C23.75 25.1928 23.75 25.6642 23.4571 25.9571C23.1642 26.25 22.6928 26.25 21.75 26.25H8.25C7.30719 26.25 6.83579 26.25 6.54289 25.9571C6.25 25.6642 6.25 25.1928 6.25 24.25V17.0585Z"
              fill="white" />
            <path
              d="M3.75 15.6366C3.75 15.9035 3.75 16.0369 3.8341 16.0781C3.91819 16.1192 4.02352 16.0373 4.23418 15.8734L13.7721 8.45502C14.362 7.99625 14.6569 7.76686 15 7.76686C15.3431 7.76686 15.638 7.99625 16.2279 8.45502L25.7658 15.8734C25.9765 16.0373 26.0818 16.1192 26.1659 16.0781C26.25 16.0369 26.25 15.9035 26.25 15.6366V14.7282C26.25 14.2478 26.25 14.0076 26.1483 13.7997C26.0466 13.5918 25.857 13.4444 25.4779 13.1495L16.2279 5.95502C15.638 5.49625 15.3431 5.26686 15 5.26686C14.6569 5.26686 14.362 5.49625 13.7721 5.95502L4.52212 13.1495C4.14295 13.4444 3.95337 13.5918 3.85168 13.7997C3.75 14.0076 3.75 14.2478 3.75 14.7282V15.6366Z"
              fill="white" />
            <path
              d="M16.125 18.75H13.875C12.7704 18.75 11.875 19.6454 11.875 20.75V26.1C11.875 26.1828 11.9422 26.25 12.025 26.25H17.975C18.0578 26.25 18.125 26.1828 18.125 26.1V20.75C18.125 19.6454 17.2296 18.75 16.125 18.75Z"
              fill="white" />
            <rect x="20" y="6.25" width="2.5" height="5" rx="0.5" fill="white" />
          </svg>
          <span>Home</span>
        </div>
        <div class="icon" @click="navigateTo({ name: 'OrgTrainings' })">
          <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M20.5837 3.625C23.4119 3.625 24.8261 3.62526 25.7048 4.50391C26.5833 5.3826 26.5837 6.79675 26.5837 9.625V19.375C26.5837 22.2033 26.5833 23.6174 25.7048 24.4961C24.8261 25.3747 23.4119 25.375 20.5837 25.375H8.41666C5.58824 25.375 4.17425 25.3748 3.29557 24.4961C2.41689 23.6174 2.41666 22.2034 2.41666 19.375V9.625C2.41666 6.79657 2.41689 5.38259 3.29557 4.50391C4.17425 3.62523 5.58824 3.625 8.41666 3.625H20.5837ZM9.66666 12.292C9.11438 12.292 8.66666 12.7397 8.66666 13.292V20.542L8.67155 20.6445C8.72303 21.1485 9.1491 21.542 9.66666 21.542C10.1842 21.542 10.6103 21.1485 10.6618 20.6445L10.6667 20.542V13.292C10.6667 12.7397 10.2189 12.292 9.66666 12.292ZM19.3337 9.875C18.7814 9.875 18.3337 10.3227 18.3337 10.875V20.542L18.3385 20.6436C18.3896 21.148 18.8158 21.542 19.3337 21.542C19.8514 21.5418 20.2778 21.1479 20.3288 20.6436L20.3337 20.542V10.875C20.3337 10.3228 19.8858 9.87518 19.3337 9.875ZM14.4997 14.708C13.9476 14.7082 13.4998 15.156 13.4997 15.708V20.541L13.5046 20.6436C13.5557 21.1477 13.982 21.5408 14.4997 21.541C15.0175 21.541 15.4436 21.1478 15.4948 20.6436L15.4997 20.541V15.708C15.4995 15.1559 15.0518 14.708 14.4997 14.708Z"
              fill="white" />
          </svg>
          <span>Trainings</span>
        </div>
        <div class="icon" @click="navigateTo({ name: 'OrgCareers' })">
          <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M22.8798 11.0484C23.4046 10.8642 23.9845 11.1431 24.1081 11.6855C24.4792 13.3134 24.4217 15.0018 23.9268 16.6191C23.4739 18.0989 22.6698 19.4685 21.5787 20.6449C21.2148 21.0372 20.6017 21.0197 20.2239 20.6408L14.9487 15.3495C14.4292 14.8284 14.6314 13.9436 15.3257 13.6999L22.8798 11.0484ZM13 4.0826C13 3.50231 13.4932 3.04057 14.0672 3.12592C15.8633 3.39302 17.5788 4.00579 19.085 4.93161C20.3794 5.72731 21.4793 6.72976 22.3343 7.87824C22.709 8.38157 22.4513 9.07932 21.8592 9.28718L14.3313 11.9301C13.6809 12.1584 13 11.6758 13 10.9865V4.0826Z"
              fill="white" />
            <path
              d="M11.9511 13.2908C11.9512 13.5763 12.0581 13.8518 12.25 14.0632L19.2677 21.7886C19.6714 22.233 19.5963 22.9337 19.0759 23.2331C18.245 23.7112 17.354 24.0924 16.4209 24.365C14.5402 24.9144 12.5476 25.0087 10.6201 24.6394C8.69236 24.2701 6.88846 23.4478 5.36911 22.2468C3.84993 21.0459 2.66126 19.5025 1.90915 17.7537C1.15711 16.0048 0.864996 14.1043 1.05759 12.2205C1.25024 10.3365 1.92171 8.52693 3.01364 6.95288C4.1056 5.37884 5.58398 4.08845 7.31735 3.19605C8.43484 2.62073 9.63633 2.22318 10.8757 2.01305C11.4515 1.91541 11.9511 2.37876 11.9511 2.96284V13.2908Z"
              fill="white" />
          </svg>
          <span>Career</span>
        </div>
        <div class="icon" @click="navigateTo({ name: 'OrgCalendar' })">
          <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M2.16675 9.4165C2.16675 7.53089 2.16675 6.58808 2.75253 6.00229C3.33832 5.4165 4.28113 5.4165 6.16675 5.4165H19.8334C21.719 5.4165 22.6618 5.4165 23.2476 6.00229C23.8334 6.58808 23.8334 7.53089 23.8334 9.4165V9.83317C23.8334 10.3046 23.8334 10.5403 23.687 10.6867C23.5405 10.8332 23.3048 10.8332 22.8334 10.8332H3.16675C2.69534 10.8332 2.45964 10.8332 2.31319 10.6867C2.16675 10.5403 2.16675 10.3046 2.16675 9.83317V9.4165Z"
              fill="white" />
            <path
              d="M22.833 13C23.3042 13 23.5401 13.0002 23.6865 13.1465C23.833 13.2929 23.833 13.5286 23.833 14V19.833C23.833 21.7186 23.8329 22.6613 23.2471 23.2471C22.6613 23.8329 21.7186 23.833 19.833 23.833H6.16699C4.28137 23.833 3.33872 23.8329 2.75293 23.2471C2.16714 22.6613 2.16699 21.7186 2.16699 19.833V14C2.16699 13.5286 2.16703 13.2929 2.31348 13.1465C2.45994 13.0002 2.69576 13 3.16699 13H22.833ZM8.58301 19.5C8.11182 19.5 7.87591 19.5001 7.72949 19.6465C7.58321 19.7929 7.58301 20.0288 7.58301 20.5V20.667C7.58301 21.1382 7.58308 21.3741 7.72949 21.5205C7.87591 21.6669 8.11182 21.667 8.58301 21.667H10.917C11.3882 21.667 11.6241 21.6669 11.7705 21.5205C11.9169 21.3741 11.917 21.1382 11.917 20.667V20.5C11.917 20.0288 11.9168 19.7929 11.7705 19.6465C11.6241 19.5001 11.3882 19.5 10.917 19.5H8.58301ZM15.083 19.5C14.6118 19.5 14.3759 19.5001 14.2295 19.6465C14.0832 19.7929 14.083 20.0288 14.083 20.5V20.667C14.083 21.1382 14.0831 21.3741 14.2295 21.5205C14.3759 21.6669 14.6118 21.667 15.083 21.667H17.417C17.8882 21.667 18.1241 21.6669 18.2705 21.5205C18.4169 21.3741 18.417 21.1382 18.417 20.667V20.5C18.417 20.0288 18.4168 19.7929 18.2705 19.6465C18.1241 19.5001 17.8882 19.5 17.417 19.5H15.083ZM8.58301 15.167C8.11182 15.167 7.87591 15.1671 7.72949 15.3135C7.58337 15.4599 7.58301 15.6959 7.58301 16.167V16.333C7.58301 16.8041 7.58337 17.0401 7.72949 17.1865C7.87591 17.3329 8.11182 17.333 8.58301 17.333H10.917C11.3882 17.333 11.6241 17.3329 11.7705 17.1865C11.9166 17.0401 11.917 16.8041 11.917 16.333V16.167C11.917 15.6959 11.9166 15.4599 11.7705 15.3135C11.6241 15.1671 11.3882 15.167 10.917 15.167H8.58301ZM15.083 15.167C14.6118 15.167 14.3759 15.1671 14.2295 15.3135C14.0834 15.4599 14.083 15.6959 14.083 16.167V16.333C14.083 16.8041 14.0834 17.0401 14.2295 17.1865C14.3759 17.3329 14.6118 17.333 15.083 17.333H17.417C17.8882 17.333 18.1241 17.3329 18.2705 17.1865C18.4166 17.0401 18.417 16.8041 18.417 16.333V16.167C18.417 15.6959 18.4166 15.4599 18.2705 15.3135C18.1241 15.1671 17.8882 15.167 17.417 15.167H15.083Z"
              fill="white" />
            <path d="M7.58325 3.25L7.58325 6.5" stroke="white" stroke-width="2" stroke-linecap="round" />
            <path d="M18.4167 3.25L18.4167 6.5" stroke="white" stroke-width="2" stroke-linecap="round" />
          </svg>
          <span>Calendar</span>
        </div>

        <div class="spacer"></div>
        <!-- pushes signout down -->
        <div class="icon signout" @click="logout">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
          <span>Sign Out</span>
        </div>
      </aside>
    </transition>

    <!-- Main content -->
    <main class="content">
      <header class="topbar">
        <div class="Pathfinder-wrapper">
          <span class="logo-text">Pathfinder</span>
        </div>
      </header>

      <!-- UPCOMING CAREERS -->
      <section class="upcoming">
        <div class="flex items-center justify-between">
          <h2 class="section-title flex items-center gap-1">
            Upcoming Careers
            <span class="count-badge">{{ sortedUpcomingCareers.length }}</span>
          </h2>
          <button class="plus-btn-text" @click="openCareerPopup">+</button>
        </div>

        <div class="career-grid">
          <div class="career-card" v-for="career in visibleUpcomingCareers" :key="career.careerID || career.id"
            @click="openCareerDetails(career)">
            <div class="career-right">
              <h3 class="career-title">{{ career.position }}</h3>
              <p class="career-deadline">Deadline: {{ formatdeadline(career.deadlineOfSubmission) }}</p>
            </div>

            <div class="menu">
              <div class="menu-icon" @click.stop="toggleUpcomingMenu(career.careerID || career.id)">⋮</div>
              <div v-if="openUpcomingMenu === (career.careerID || career.id)" class="dropdown-menu" @click.stop>
                <ul>
                  <li @click.stop="openApplicantsModal">Applicants</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <button v-if="sortedUpcomingCareers.length > 4" class="show-more-btn"
          @click="showAllUpcoming = !showAllUpcoming">
          {{ showMoreUpcoming ? 'Show Less' : 'Show More' }}
        </button>
      </section>

      <!-- COMPLETED CAREERS -->
      <section class="completed">
        <div class="flex items-center justify-between">
          <h2 class="section-title flex items-center gap-1">
            Completed Careers
            <span class="count-badge">{{ sortedCompletedCareers.length }}</span>
          </h2>
        </div>

        <div class="career-grid">
          <div class="career-card" v-for="career in visibleCompletedCareers" :key="career.careerID || career.id"
            @click="openCareerDetails(career)">
            <div class="career-right">
              <h3 class="career-title">{{ career.title || career.position }}</h3>
              <p class="career-deadline">Closed: {{ formatdeadline(career.deadlineOfSubmission) }}</p>
            </div>

            <div class="menu">
              <div class="menu-icon" @click.stop="toggleCompletedMenu(career.careerID || career.id)">⋮</div>
              <div v-if="openCompletedMenu === (career.careerID || career.id)" class="dropdown-menu" @click.stop>
                <ul>
                  <li @click.stop="openApplicantsModal">Applicants</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <button v-if="sortedCompletedCareers.length > 4" class="show-more-btn"
          @click="showAllCompleted = !showAllCompleted">
          {{ showAllCompleted ? 'Show Less' : 'Show More' }}
        </button>
      </section>


      <!-- Applicants Modal -->
      <div v-if="showApplicantsModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
          <button class="modal-close-btn" @click="closeModal">✕</button>

          <h3 class="modal-title">Applicants for {{ selectedCareer.title }}</h3>

          <div class="applicants-table-container">
            <table>
              <thead>
                <tr>
                  <th>FULL NAME</th>
                  <th>DATE SUBMITTED</th>
                  <th>APPLICATION STATUS</th>
                  <th>REQUIREMENTS</th>
                  <th>INTERVIEW SCHEDULING</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="person in applicantsList" :key="person.id">
                  <td>
                    <p class="applicant-name">{{ person.name }}</p>
                  </td>
                  <td>
                    <p class="application-date">{{ person.dateSubmitted }}</p>
                  </td>

                  <td :class="{
                    'status-for-review': person.status === 'For Review',
                    'status-scheduled': person.status === 'Interview Scheduled',
                    'status-hired': person.status === 'Hired',
                    'status-rejected': person.status === 'Rejected'
                  }">
                    {{ person.status }}
                  </td>

                  <!-- Requirements -->
                  <td class="requirements-col">
                    <div v-if="person.requirementsSubmitted">
                      <button class="view-btn" @click="viewRequirements(person)">View</button>
                      <span class="check-icon">✅</span>
                    </div>
                    <div v-else>
                      <button class="upload-btn" @click="uploadRequirements(person)">Nakalimutan ko ilalagay</button>
                    </div>
                  </td>

                  <!-- Interview Scheduling -->
                  <td>
                    <!-- For Review -->
                    <button v-if="person.status === 'For Review'" class="schedule-btn"
                      @click="openScheduleModal(person)">
                      Schedule
                    </button>

                    <!-- Interview Scheduled -->
                    <button v-else-if="person.status === 'Interview Scheduled'" class="schedule-btn view"
                      @click="openViewScheduleModal(person)">
                      View Schedule
                    </button>

                    <!-- Hired -->
                    <button v-else-if="person.status === 'Hired'" class="schedule-btn hired" disabled>
                      Hired
                    </button>

                    <!-- Rejected -->
                    <button v-else-if="person.status === 'Rejected'" class="schedule-btn rejected" disabled>
                      Rejected
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Schedule Modal -->
      <div v-if="showScheduleModal" class="modal-overlay" @click.self="closeScheduleModal">
        <div class="modal-box">
          <button class="modal-close-btn" @click="closeScheduleModal">✕</button>
          <h3>Schedule Interview for {{ selectedPerson?.name }}</h3>

          <div class="schedule-form">
            <!-- Date & Time -->
            <label for="scheduleDate">Date & Time:</label>
            <div class="date-input-wrapper">
              <input id="scheduleDate" ref="dateInput" type="datetime-local" v-model="scheduleData.date"
                @keydown.prevent @keypress.prevent @paste.prevent @input="$event.target.value = $event.target.value" />
              <span class="calendar-icon" @click.prevent="openCalendar">
                <!-- SVG icon (click target is the span) -->
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M2.16669 9.41675C2.16669 7.53113 2.16669 6.58832 2.75247 6.00253C3.33826 5.41675 4.28107 5.41675 6.16669 5.41675H19.8334C21.719 5.41675 22.6618 5.41675 23.2476 6.00253C23.8334 6.58832 23.8334 7.53113 23.8334 9.41675V9.83342C23.8334 10.3048 23.8334 10.5405 23.6869 10.687C23.5405 10.8334 23.3048 10.8334 22.8334 10.8334H3.16669C2.69528 10.8334 2.45958 10.8334 2.31313 10.687C2.16669 10.5405 2.16669 10.3048 2.16669 9.83341V9.41675Z"
                    fill="black" />
                  <path
                    d="M22.833 13C23.3042 13 23.5401 13.0002 23.6865 13.1465C23.833 13.2929 23.833 13.5286 23.833 14V19.833C23.833 21.7186 23.8329 22.6613 23.2471 23.2471C22.6613 23.8329 21.7186 23.833 19.833 23.833H6.16699C4.28137 23.833 3.33872 23.8329 2.75293 23.2471C2.16714 22.6613 2.16699 21.7186 2.16699 19.833V14C2.16699 13.5286 2.16703 13.2929 2.31348 13.1465C2.45994 13.0002 2.69576 13 3.16699 13H22.833Z"
                    fill="black" />
                  <path d="M7.58331 3.25L7.58331 6.5" stroke="black" stroke-width="2" stroke-linecap="round" />
                  <path d="M18.4167 3.25L18.4167 6.5" stroke="black" stroke-width="2" stroke-linecap="round" />
                </svg>
              </span>
            </div>


            <!-- Interview Mode -->
            <div class="mode-selection">
              <label><input type="radio" value="onSite" v-model="scheduleData.mode" /> On-Site</label>
              <label><input type="radio" value="online" v-model="scheduleData.mode" /> Online</label>
            </div>

            <!-- Conditional Fields -->
            <div v-if="scheduleData.mode === 'onSite'">
              <label>Location:</label>
              <input type="text" v-model="scheduleData.detail" placeholder="Enter interview location" />
            </div>

            <div v-else-if="scheduleData.mode === 'online'">
              <label>Interview Link:</label>
              <input type="text" v-model="scheduleData.detail" placeholder="https://meet.google.com/..." />
            </div>

            <div class="modal-actions">
              <button class="confirm-btn" @click="confirmSchedule">Confirm</button>
              <button class="cancel-btn" @click="closeScheduleModal">Cancel</button>
            </div>
          </div>
        </div>
      </div>


      <!-- View Schedule Modal -->
      <div v-if="showViewScheduleModal" class="modal-overlay" @click.self="closeViewScheduleModal">
        <div class="modal-box">
          <button class="modal-close-btn" @click="closeViewScheduleModal">✕</button>
          <h3>Interview Schedule for {{ selectedPerson?.name }}</h3>

          <div class="view-schedule-info">
            <p><strong>Date:</strong> {{ selectedPerson?.interviewDate }}</p>
            <p><strong>Time:</strong> {{ selectedPerson?.interviewTime }}</p>
            <p><strong>Mode:</strong> {{ selectedPerson?.mode }}</p>

            <p v-if="selectedPerson?.mode === 'On-Site'">
              <strong>Location:</strong> {{ selectedPerson?.linkOrLocation }}
            </p>
            <p v-else-if="selectedPerson?.mode === 'Online'">
              <strong>Interview Link:</strong>
              <a :href="selectedPerson?.linkOrLocation" target="_blank">
                {{ selectedPerson?.linkOrLocation }}
              </a>
            </p>
          </div>

          <div class="modal-actions">
            <button class="cancel-btn" @click="closeViewScheduleModal">Close</button>
          </div>
        </div>
      </div>


      <!-- Career Popup Modal -->
      <div v-if="showCareerPopup" class="career-popup-overlay">
        <div class="career-popup">
          <!-- Close Button -->
          <button @click="closeCareerPopup" class="career-popup-close">
            ✕
          </button>

          <!-- Title -->
          <h2 class="career-popup-title">Post Career</h2>

          <!-- Form -->
          <form @submit.prevent="saveCareer" class="Career-popup-form">
            <input v-model="newCareer.position" type="text" placeholder="Position" class="career-input" />
            <input v-model="newCareer.details" type="text" placeholder="Details and Instruction" class="career-input" />
            <textarea v-model="newCareer.qualifications" placeholder="Qualifications" class="career-input"></textarea>
            <textarea v-model="newCareer.requirements" placeholder="Requirements" class="career-input"></textarea>
            <input v-model="newCareer.letterAddress" type="text" placeholder="Application Letter Address"
              class="career-input" />
            <!-- Deadline input with calendar icon (career) -->
            <div class="deadline-input-wrapper">
              <input type="date" v-model="newCareer.deadline" :min="todayDate" placeholder="Deadline of Submission"
                class="career-input" />
              <span class="calendar-icon">
                <!-- use the same SVG you shared -->
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M2.16669 9.4165C2.16669 7.53089 2.16669 6.58808 2.75247 6.00229C3.33826 5.4165 4.28107 5.4165 6.16669 5.4165H19.8334C21.719 5.4165 22.6618 5.4165 23.2476 6.00229C23.8334 6.58808 23.8334 7.53089 23.8334 9.4165V9.83317C23.8334 10.3046 23.8334 10.5403 23.6869 10.6867C23.5405 10.8332 23.3048 10.8332 22.8334 10.8332H3.16669C2.69528 10.8332 2.45958 10.8332 2.31313 10.687C2.16669 10.5405 2.16669 10.3048 2.16669 9.83317V9.4165Z"
                    fill="#4a4a4a" />
                  <path
                    d="M22.833 13C23.3042 13 23.5401 13.0002 23.6865 13.1465C23.833 13.2929 23.833 13.5286 23.833 14V19.833C23.833 21.7186 23.8329 22.6613 23.2471 23.2471C22.6613 23.8329 21.7186 23.833 19.833 23.833H6.16699C4.28137 23.833 3.33872 23.8329 2.75293 23.2471C2.16714 22.6613 2.16699 21.7186 2.16699 19.833V14C2.16699 13.5286 2.16703 13.2929 2.31348 13.1465C2.45994 13.0002 2.69576 13 3.16699 13H22.833ZM8.58301 19.5C8.11182 19.5 7.87591 19.5001 7.72949 19.6465C7.58321 19.7929 7.58301 20.0288 7.58301 20.5V20.667C7.58301 21.1382 7.58308 21.3741 7.72949 21.5205C7.87591 21.6669 8.11182 21.667 8.58301 21.667H10.917C11.3882 21.667 11.6241 21.6669 11.7705 21.5205C11.9169 21.3741 11.917 21.1382 11.917 20.667V20.5C11.917 20.0288 11.9168 19.7929 11.7705 19.6465C11.6241 19.5001 11.3882 19.5 10.917 19.5H8.58301ZM15.083 19.5C14.6118 19.5 14.3759 19.5001 14.2295 19.6465C14.0832 19.7929 14.083 20.0288 14.083 20.5V20.667C14.083 21.1382 14.0831 21.3741 14.2295 21.5205C14.3759 21.6669 14.6118 21.667 15.083 21.667H17.417C17.8882 21.667 18.1241 21.6669 18.2705 21.5205C18.4169 21.3741 18.417 21.1382 18.417 20.667V20.5C18.417 20.0288 18.4168 19.7929 18.2705 19.6465C18.1241 19.5001 17.8882 19.5 17.417 19.5H15.083ZM8.58301 15.167C8.11182 15.167 7.87591 15.1671 7.72949 15.3135C7.58337 15.4599 7.58301 15.6959 7.58301 16.167V16.333C7.58301 16.8041 7.58337 17.0401 7.72949 17.1865C7.87591 17.3329 8.11182 17.333 8.58301 17.333H10.917C11.3882 17.333 11.6241 17.3329 11.7705 17.1865C11.9166 17.0401 11.917 16.8041 11.917 16.333V16.167C11.917 15.6959 11.9166 15.4599 11.7705 15.3135C11.6241 15.1671 11.3882 15.167 10.917 15.167H8.58301ZM15.083 15.167C14.6118 15.167 14.3759 15.1671 14.2295 15.3135C14.0834 15.4599 14.083 15.6959 14.083 16.167V16.333C14.083 16.8041 14.0834 17.0401 14.2295 17.1865C14.3759 17.3329 14.6118 17.333 15.083 17.333H17.417C17.8882 17.333 18.1241 17.3329 18.2705 17.1865C18.4166 17.0401 18.417 16.8041 18.417 16.333V16.167C18.417 15.6959 18.4166 15.4599 18.2705 15.3135C18.1241 15.1671 17.8882 15.167 17.417 15.167H15.083Z"
                    fill="#4a4a4a" />
                  <path d="M7.58331 3.25L7.58331 6.5" stroke="#4a4a4a" stroke-width="2" stroke-linecap="round" />
                  <path d="M18.4167 3.25L18.4167 6.5" stroke="#4a4a4a" stroke-width="2" stroke-linecap="round" />
                </svg>
              </span>
            </div>

         <!-- Tag selection -->
            <div class="relative mb-4">
            <label class="block font-semibold text-gray-600 mb-2">Tags</label>
            <!-- Tag List Wrapper for horizontal scrolling -->
            <div class="tag-list-wrapper">
            <div class="tag-list">
            <span
            v-for="tag in tagOptions"
            :key="tag.TagID"
            class="tag-chip"
            @click="toggleTag(tag.TagID)"
            :class="{ 'tag-chip-selected': isTagSelected(tag.TagID) }"
            >
            {{ tag.TagName }}
            </span>
            </div>
            </div>
            <!-- Display Selected Tags as Chips -->
            <div v-if="newCareer.Tags.length" class="flex flex-wrap gap-2 mt-2">
            <span
            v-for="tagID in newCareer.Tags"
            :key="tagID"
            class="bg-blue-100 text-blue-700 text-sm px-3 py-1 rounded-full"
            >
            {{ getTagName(tagID) }}
            <button @click.stop="removeTag(tagID)" class="ml-1 text-red-500">×</button> <!-- Remove Button -->
            </span>
            </div>
            <!-- Input for New Tag -->
            <input
            v-model="newTagName"
            type="text"
            placeholder="Add new tag"
            class="career-input mt-2"
            />
            <button @click.prevent="addTag" class="career-save-btn mt-2">Add Tag</button>
            </div>

            <!-- Save -->
            <button type="submit" class="career-save-btn">Post</button>
          </form>
        </div>
      </div>

      <!-- Career Details Modal -->
      <div v-if="showCareerDetailsModal" class="modal-overlay" @click.self="closeCareerDetails">
        <div class="career-details-modal">
          <button class="modal-close-btn" @click="closeCareerDetails">✕</button>
          <h3 class="modal-title">{{ selectedCareer.title }}</h3>
          <p class="career-info"><strong>Position:</strong> {{ selectedCareer.position }}</p>
          <p class="career-info"><strong>Details:</strong> {{ selectedCareer.detailsAndInstructions }}</p>
          <p class="career-info"><strong>Qualifications:</strong> {{ selectedCareer.qualifications }}</p>
          <p class="career-info"><strong>Requirements:</strong> {{ selectedCareer.requirements }}</p>
          <p class="career-info"><strong>Letter Address:</strong> {{ selectedCareer.applicationLetterAddress }}</p>
          <p class="career-info"><strong>Deadline:</strong> {{ formatdeadline(selectedCareer.deadlineOfSubmission) }}
          </p>
          <div class="career-actions">
            <button class="btn-view-applicants" @click="handleViewApplicants">
              View Applicants
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import dictLogo from "@/assets/images/DICT-Logo-icon_only (1).png";
import { ref, onMounted } from 'vue';
import axios from "axios";
import api from "@/api/axios";

const fetchTags = async () => {
  try {
    const response = await axios.get(import.meta.env.VITE_API_BASE_URL +'/tags');
    console.log(response.data); // Log the API response
    this.tagOptions = response.data; // Update tagOptions
  } catch (error) {
    console.error('Error fetching tags:', error);
  }
}


export default {
  data() {
    return {
      newTagName: '',
      dictLogo,
      openUpcomingMenu: null,
      openCompletedMenu: null,

      showCareerDetailsModal: false,
      selectedCareer: {},

      showApplicantsModal: false,
      showViewScheduleModal: false,
      showScheduleModal: false,
      selectedPerson: null,

      showAllUpcoming: false,
      showAllCompleted: false,

      scheduleData: {
        date: "",
        mode: "",
        detail: "",
      },

      applicantsList: [
        {
          id: 1,
          name: "John Doe",
          dateSubmitted: "September 10, 2025",
          status: "For Review",
          requirements: ["Resume", "Cover Letter", "Portfolio"]
        },
        {
          id: 2,
          name: "Maria Santos",
          dateSubmitted: "September 12, 2025",
          status: "Rejected",
          requirements: ["Resume", "Transcript of Records"]
        },
        {
          id: 3,
          name: "David Cruz",
          dateSubmitted: "September 14, 2025",
          status: "Interview Scheduled",
          requirements: ["Resume", "Character Reference"]
        },
        {
          id: 4,
          name: "Anna Lee",
          dateSubmitted: "September 16, 2025",
          status: "Hired",
          requirements: ["Resume", "NBI Clearance", "Medical Certificate"]
        },
        {
          id: 5,
          name: "Mark Reyes",
          dateSubmitted: "September 18, 2025",
          status: "Rejected",
          requirements: ["Resume", "Portfolio"]
        },
        {
          id: 6,
          name: "Sophia Tan",
          dateSubmitted: "September 19, 2025",
          status: "For Review",
          requirements: ["Resume", "Cover Letter"]
        },
        {
          id: 7,
          name: "James Lim",
          dateSubmitted: "September 20, 2025",
          status: "Interview Scheduled",
          requirements: ["Resume", "Character Reference", "Transcript of Records"]
        },
        {
          id: 8,
          name: "Christine Dela Cruz",
          dateSubmitted: "September 21, 2025",
          status: "Rejected",
          requirements: ["Resume", "Portfolio"]
        },
        {
          id: 9,
          name: "Robert Mendoza",
          dateSubmitted: "September 22, 2025",
          status: "For Review",
          requirements: ["Resume", "Cover Letter"]
        },
        {
          id: 10,
          name: "Isabella Garcia",
          dateSubmitted: "September 25, 2025",
          status: "Hired",
          requirements: ["Resume", "NBI Clearance", "Medical Certificate"]
        },
      ],


      showApplicantsModal: false,
      upcomingCareers: [],

      

      // Popup state + form
      showCareerPopup: false,
      newCareer: {
        position: "",
        details: "",
        qualifications: "",
        requirements: "",
        letterAddress: "",
        deadline: "",
        Tags: []
      },
      tagOptions: []
     
      

    };
  },
  

  methods: {
    checkToken() {
      const token = localStorage.getItem("token");
      if (!token) {
        alert("🔒 Please log in to continue.");
        return false; // Token is not valid
      }
      return token; // Token is valid
    },
    
    async addTag() {
      if (!this.newTagName) {
        alert("Please enter a tag name.");
        return;
      }

      const token = this.checkToken(); // Check token before proceeding
      if (!token) return; // If token is invalid, exit

      try {
        // Send the new tag to the backend
        const response = await axios.post(import.meta.env.VITE_API_BASE_URL +'/tags', {
          TagName: this.newTagName
        }, {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json"
          }
        });

        // Add the new tag to the tagOptions and newCareer.Tags
        this.tagOptions.push(response.data);
        this.newCareer.Tags.push(response.data.TagID);

        // Clear the input field
        this.newTagName = '';
        alert("Tag added successfully!");
      } catch (error) {
        console.error("Error adding tag:", error);
        alert("Failed to add tag.");
      }
    },


     // Toggle selection of a tag
      toggleTag(tagID) {
        const index = this.newCareer.Tags.indexOf(tagID);
        if (index === -1) {
          // If the tag is not selected, add it
          this.newCareer.Tags.push(tagID);
        } else {
          // If the tag is already selected, remove it
          this.newCareer.Tags.splice(index, 1);
        }
      },

      // Check if a tag is selected
      isTagSelected(tagID) {
        return this.newCareer.Tags.includes(tagID);
      },

      // Remove a tag directly from the selected tags
      removeTag(tagID) {
        const index = this.newCareer.Tags.indexOf(tagID);
        if (index !== -1) {
          this.newCareer.Tags.splice(index, 1);
        }
      },

    async fetchTags() {
      try {
        const response = await axios.get(import.meta.env.VITE_API_BASE_URL +'/tags');
        this.tagOptions = response.data; // Update tagOptions correctly
        console.log(this.tagOptions); // Log the tags to see if they are fetched correctly
      } catch (error) {
        console.error('Error fetching tags:', error);
      }
    },

    getTagName(id) {
      const tag = this.tagOptions.find(t => t.TagID === id);
      return tag ? tag.TagName : '';
    },


    openCareerPopup() {
      this.showCareerPopup = true;
      this.fetchTags(); // 👈 Load tags into dropdown
    },
    

    toggleUpcomingMenu(id) {
      this.openUpcomingMenu = this.openUpcomingMenu === id ? null : id;
      this.openCompletedMenu = null;
    },
    toggleCompletedMenu(id) {
      this.openCompletedMenu = this.openCompletedMenu === id ? null : id;
      this.openUpcomingMenu = null;
    },
    handleClickOutside(event) {
      const clickedInside = event.target.closest(".menu-icon, .dropdown-menu");
      if (!clickedInside) {
        this.openUpcomingMenu = null;
        this.openCompletedMenu = null;
      }
    },

    openApplicantsModal() {
      this.showApplicantsModal = true;
    },
    closeModal() {
      this.showApplicantsModal = false;
    },

    showMoreUpcoming() {
      this.visibleUpcomingCount += 4; // show 4 more
    },
    showMoreCompleted() {
      this.visibleCompletedCount += 4;
    },

    // ✅ OPEN SCHEDULE MODAL (only when status = For Review)
    openScheduleModal(person) {
      if (person.status === "For Review") {
        this.selectedPerson = person;
        this.showScheduleModal = true;
        this.showViewScheduleModal = false;
      }
    },
    closeScheduleModal() {
      this.showScheduleModal = false;
      this.scheduleData = { date: "", onSite: false, online: false, link: "" };
    },

    // ✅ OPEN VIEW SCHEDULE MODAL (only when status = Interview Scheduled)
    openViewScheduleModal(person) {
      if (person.status === "Interview Scheduled") {
        this.selectedPerson = person;
        this.showViewScheduleModal = true;
        this.showScheduleModal = false;
      }
    },
    closeViewScheduleModal() {
      this.showViewScheduleModal = false;
    },
    openCalendar() {
      const input = this.$refs.dateInput;
      if (!input) {
        console.warn("dateInput ref not found");
        return;
      }

      // Try the modern showPicker() first (works in some browsers)
      try {
        if (typeof input.showPicker === "function") {
          input.showPicker();
          return;
        }
      } catch (err) {
        console.debug("showPicker() failed:", err);
      }

      // Next try focusing then clicking (some browsers open picker on click/focus)
      try {
        input.focus();
        // Clicking the input sometimes triggers the native UI
        input.click();
        return;
      } catch (err) {
        console.debug("input.click()/focus failed:", err);
      }

      // Final fallback: open a small helper so user can still pick — alert as last resort
      console.warn("Native picker was not opened programmatically by the browser. Consider adding a JS datepicker as a reliable fallback.");
    },

    confirmSchedule() {
      if (!this.scheduleData.date) {
        alert("Please select a date and time.");
        return;
      }

      if (!this.scheduleData.mode) {
        alert("Please select interview mode (On-Site or Online).");
        return;
      }

      if (!this.scheduleData.detail) {
        alert(
          this.scheduleData.mode === "onSite"
            ? "Please enter the interview location."
            : "Please enter the interview link."
        );
        return;
      }

      const interviewDate = new Date(this.scheduleData.date);
      this.selectedPerson.interviewDate = interviewDate.toLocaleDateString();
      this.selectedPerson.interviewTime = interviewDate.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });
      this.selectedPerson.mode =
        this.scheduleData.mode === "onSite" ? "On-Site" : "Online";
      this.selectedPerson.linkOrLocation = this.scheduleData.detail;
      this.selectedPerson.status = "Interview Scheduled";

      this.closeScheduleModal();
    },

    viewRequirements(person) {
      alert(`Viewing requirements for ${person.name}`);
    },
    uploadRequirements(person) {
      alert(`Uploading requirements for ${person.name}`);
    },

    async fetchCareers() {
      try {
        const response = await axios.get(import.meta.env.VITE_API_BASE_URL +"/careers");
        this.upcomingCareers = response.data;
      } catch (error) {
        console.error("ERROR FETCHING CAREERS:", error);
      }
    },

    openCareerPopup() {
      this.showCareerPopup = true;
    },
    closeCareerPopup() {
      this.showCareerPopup = false;
      this.resetNewCareer();
    },
    openCareerDetails(Career) {
      this.selectedCareer = Career;
      this.showCareerDetailsModal = true;
    },
    closeCareerDetails() {
      this.showCareerDetailsModal = false;
    },
    handleViewApplicants() {
      this.closeCareerDetails();
      this.openApplicantsModal();
    },

   async saveCareer() {
  try {
    // 🔹 0. Get token and trim
    const token = localStorage.getItem("token")?.trim();
    console.log("🔹 Token from localStorage:", `"${token}"`); // quotes to detect spaces

    if (!token) {
      alert("Please log in to continue.");
      return;
    }

    // 🔹 1. Validate required fields
    const requiredFields = [
      "position",
      "details",
      "qualifications",
      "requirements",
      "letterAddress",
      "deadline"
    ];

    for (const field of requiredFields) {
      if (!this.newCareer[field]) {
        alert(`Please fill out the field: ${field}`);
        return;
      }
    }

    // 🔹 2. Optional tags check
    if (!this.newCareer.Tags || this.newCareer.Tags.length === 0) {
      const proceed = confirm("No tags selected. Continue without tags?");
      if (!proceed) return;
    }

    // 🔹 3. Prepare payload
    const payload = {
      position: this.newCareer.position,
      details: this.newCareer.details,
      qualifications: this.newCareer.qualifications,
      requirements: this.newCareer.requirements,
      letterAddress: this.newCareer.letterAddress,
      deadline: this.newCareer.deadline,
      Tags: this.newCareer.Tags || []
    };

    console.log("🔹 Payload to be sent:", payload);

    // 🔹 4. Send POST request with Authorization header
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/careers",
      payload,
      {
        headers: {
          Authorization: `Bearer ${token}`, // use trimmed token
          "Content-Type": "application/json"
        }
      }
    );

    // 🔹 5. Handle success
    if (response?.status >= 200 && response?.status < 300 && response?.data?.data) {
      console.log("✅ Response received:", response.data);
      const newCareer = response.data.data;
      this.upcomingCareers.push(newCareer);
      alert("Career posted successfully!");
      this.closeCareerPopup();
      this.resetNewCareer();
      await this.fetchCareers();
    } else {
      console.warn("⚠️ Unexpected response:", response);
      alert("Something went wrong while saving the career.");
    }

  } catch (error) {
    console.error("❌ ERROR SAVING CAREER:", error);

    // 🔹 Axios error handling
    if (error.response) {
      console.log("🔹 Server responded with:", error.response.data);
      switch (error.response.status) {
        case 401:
          alert(
            "Unauthorized: Please check your token. Make sure your token matches the organization token exactly and has no extra spaces."
          );
          break;
        case 422:
          alert("Validation failed. Check your inputs.");
          break;
        case 409:
          alert("This career already exists!");
          break;
        default:
          alert("Server error: Please try again.");
      }
    } else if (error.request) {
      alert("No response from server. Check your network or server.");
    } else {
      alert("Error: " + error.message);
    }
  }
},
    resetNewCareer() {
      this.newCareer = {
        position: "",
        details: "",
        qualifications: "",
        requirements: "",
        letterAddress: "",
        deadline: "",
        Tags: []
      };
    },

    formatdeadline(deadline) {
      if (!deadline) return "No deadline set";
      try {
        const date = new Date(deadline);
        return date.toLocaleString("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric",
        });
      } catch (error) {
        return deadline;
      }
    },
  },

  computed: {
    todayDate() {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, "0");
      const day = String(today.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`; // YYYY-MM-DD
    },
    visibleUpcomingCareers() {
      const list = this.sortedUpcomingCareers;
      return this.showAllUpcoming ? list : list.slice(0, 4);
    },
    visibleCompletedCareers() {
      const list = this.sortedCompletedCareers;
      return this.showAllCompleted ? list : list.slice(0, 4);
    },

    sortedUpcomingCareers() {
      const now = new Date();
      const list = Array.isArray(this.upcomingCareers) ? this.upcomingCareers : [];
      return list
        .filter(c => {
          if (!c.deadlineOfSubmission) return true;
          const d = new Date(c.deadlineOfSubmission);
          return d >= now; // only future deadlines
        })
        .sort((a, b) => new Date(a.deadlineOfSubmission) - new Date(b.deadlineOfSubmission));
    },

    sortedCompletedCareers() {
      const now = new Date();
      const list = Array.isArray(this.upcomingCareers) ? this.upcomingCareers : [];
      return list
        .filter(c => {
          if (!c.deadlineOfSubmission) return false;
          const d = new Date(c.deadlineOfSubmission);
          return d < now; // only past deadlines
        })
        .sort((a, b) => new Date(b.deadlineOfSubmission) - new Date(a.deadlineOfSubmission));
    },
  },

  

  mounted() {
    this.fetchCareers();
    this.fetchTags();
    document.addEventListener("click", this.handleClickOutside);
  },

  beforeUnmount() {
    document.removeEventListener("click", this.handleClickOutside);
  },

  
};
</script>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from 'vue-router';

const router = useRouter();
const isSidebarOpen = ref(true);
const organizationName = ref("");


// Toggle sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

// For On-Going Careers
const openUpcomingMenu = ref(null);
function toggleUpcomingMenu(id) {
  openUpcomingMenu.value = openUpcomingMenu.value === id ? null : id;
}

// For Filled Out Careers
const openCompletedMenu = ref(null);
function toggleCompletedMenu(id) {
  openCompletedMenu.value = openCompletedMenu.value === id ? null : id;
}

// Close all dropdowns when clicking outside
function closeAllMenus() {
  openUpcomingMenu.value = null;
  openCompletedMenu.value = null;
}
// Get org name from localStorage on mount
onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    const user = JSON.parse(storedUser);
    if (user.role === "organization") {
      organizationName.value = user.displayName || user.name;
    }
  }
  document.addEventListener("click", closeAllMenus);
});
onBeforeUnmount(() => {
  document.removeEventListener("click", closeAllMenus);
});

// Sidebar navigation functions
const goToProfile = () => router.push('/profile');
const goToHome = () => router.push('/organization');
const goToTrainings = () => router.push({ name: 'OrgTrainings' });
const goToCareers = () => router.push({ name: 'OrgCareers' });
const goToCalendar = () => router.push({ name: 'OrgCalendar' });

// Generic navigation function
const navigateTo = (route) => {
  router.push(route);
}

const logout = () => {
  localStorage.removeItem('user');
  localStorage.removeItem('token');
  router.push({ name: 'Login' });
};
</script>



<style scoped>

.tag-list-wrapper {
  overflow-x: auto; 
  white-space: nowrap; 
  padding: 5px 0; 
}

.tag-list {
  display: flex; 
}


.tag-chip {
  cursor: pointer;
  background-color: #e2e8f0; 
  padding: 8px 12px; 
  border-radius: 16px;
  margin-right: 8px; 
  transition: background-color 0.2s;
  flex-shrink: 0; 
}

.tag-chip:hover {
  background-color: #cbd5e0; 
}

.tag-chip-selected {
  background-color: #4a5568; 
  color: white; 
}

/* Optional fade animation */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.organization-careers {
  display: flex;
  height: 100vh;
  font-family: 'Poppins', sans-serif;
  background-color: #f4f4f4;
}

/* Sidebar */
.sidebar {
  width: 200px;
  /* Expanded width */
  background-color: #44576d;
  color: white;
  display: flex;
  flex-direction: column;
  align-items: start;
  padding: 20px 10px;
  transition: width 0.3s ease-in;
  overflow: hidden;
}

.sidebar.collapsed {
  width: 60px;
  /* Collapsed width */
}

.sidebar .icon {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 20px 0;
  font-size: 16px;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  width: 100%;
  white-space: nowrap;
  transition: background-color 0.2s ease;
}

.sidebar .icon svg {
  flex-shrink: 0;
  /* Ensures icon doesn't shrink */
  width: 30px;
  height: 30px;
}

.sidebar .icon:hover {
  background-color: #4a5568;
}

.sidebar .icon span {
  transition: opacity 0.3s ease;
  opacity: 1;
}

.sidebar.collapsed .icon span {
  opacity: 0;
  pointer-events: none;
}

.sidebar.collapsed .avatar {
  margin: 10px auto;
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.sidebar.collapsed .space {
  margin: 10px auto;
  width: 40px;
  height: 5px;
  border-radius: 50%;
}

.icon.active {
  background-color: #ffffff33;
  /* faint highlight */
  color: #000000;
}

.icon.active svg path {
  fill: #000000;
  /* change icon color when active */
}

/* Main Content */
.content {
  flex: 1;
  padding: 30px 40px;
  overflow-y: auto;
}

.topbar {
  display: flex;
  justify-content: center;
  /* Keep it centered */
  align-items: center;
  margin-bottom: 40px;
}

.logo-title {
  font-size: 32px;
  font-weight: 700;
  color: #2d3748;
}

.logo-text {
  font-size: 26px;
  font-weight: 700;
  color: #44576d;
  font-family: 'Poppins', sans-serif;
}

.search-container {
  position: relative;
  width: 500px;
  max-width: 100%;
}

.section-block {
  margin: 50px auto;
  max-width: 1000px;
  padding: 0 20px;
  text-align: center;
}

.section-block h2 {
  font-size: 26px;
  color: #2d3748;
  margin-bottom: 20px;
  text-align: center;
}

.applicants-btn {
  background-color: #44576d;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
}

.applicants-btn {
  background-color: #44576d;
}

.applicants-btn:hover {
  background-color: #5a667d;
}

.spacer {
  flex: 1;
  /* pushes signout to bottom */
}

.sidebar .signout {
  margin-top: auto;
  /* ensures it's at the bottom */
}

.profile-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  /* centers everything under avatar */
  text-align: center;
  gap: 0.3rem;
  /* spacing between avatar, name, and actions */
}

.profile-section .avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background-color: #ccc;
  /* placeholder for logo/avatar */
}

.avatar {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background-color: #ccc;
  /* Placeholder, replace with image if needed */
  margin: 0 auto 10px auto;
}

.org-name {
  font-weight: 600;
  font-size: 14px;
  text-align: center;
}

.profile-actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.profile-actions .action {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  /* smaller space between icon + text */
  font-size: 13px;
  /* smaller text */
  color: #fff;
  /* keep text color consistent */
  cursor: pointer;
}

.profile-actions .action svg {
  width: 14px;
  /* shrink the icon */
  height: 14px;
}

.profile-actions .action:hover {
  opacity: 0.8;
}

/* Training and Job Offer Area*/

.career-slider {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  /* 4 per row on large screens */
  gap: 1.5rem;
  /* space between cards */
  margin-top: 1rem;
}

.career-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  /* 4 per row if enough space */
  gap: 1rem;
}

.upcoming {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.completed {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 30px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 15px;
  color: #2d3748;
}

.career-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  transition: transform 0.2s ease;
}

.career-card:hover {
  transform: translateY(-4px);
}

.career-left {
  margin-right: 12px;
}


.career:last-child {
  border-bottom: none;
}

.career-title {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 4px;
  color: #2d3748;
}

.career-deadline {
  font-size: 13px;
  color: #4a5568;
}

.career-company {
  font-size: 14px;
  color: #718096;
  margin-bottom: 10px;
}

.edit-btn {
  background-color: #44576d;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
}

.apply-btn:hover {
  background-color: #5a667d;
}

.menu {
  position: absolute;
  top: 10px;
  right: 10px;
}

.menu-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 20px;
  color: #000;
}

.menu-icon {
  cursor: pointer;
  font-size: 20px;
  color: #000;
  position: absolute;
  top: 2px;
  right: 5px;
}

.menu-dropdown {
  position: absolute;
  top: 30px;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  padding: 8px 0;
  width: 120px;
  z-index: 10;
}

.menu-dropdown p {
  margin: 0;
  padding: 8px 12px;
  font-size: 14px;
  cursor: pointer;
}

.menu-dropdown p:hover {
  background: #f7fafc;
}

.dropdown-menu {
  position: absolute;
  top: 25px;
  right: 0;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  min-width: 160px;
  overflow: hidden;
}

.dropdown-menu ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.dropdown-menu li {
  padding: 6px 12px;
  cursor: pointer;
  font-size: 14px;
  white-space: nowrap;
  color: #000;
  line-height: 1.4;
}

.dropdown-menu li+li {
  border-top: 1px solid #eee;
}

.dropdown-menu li:hover {
  background: #f7f7f7;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal {
  background: white;
  padding: 25px;
  border-radius: 15px;
  width: 350px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 15px;
  gap: 10px;
}

.confirm-btn {
  background-color: #334155;
  color: #fff;
  border: none;
  padding: 6px 14px;
  border-radius: 10px;
  cursor: pointer;
}

.cancel-btn {
  background-color: #334155;
  color: #111;
  border: none;
  padding: 6px 14px;
  border-radius: 10px;
  cursor: pointer;
}

.modal-content {
  position: relative;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 1100px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.Applicants-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 20px;
  /* more space between cards */
  margin-top: 15px;
}

.registrant-card {
  background: #ffffff;
  /* solid white card */
  border: 1px solid #ddd;
  /* light gray border */
  border-radius: 12px;
  padding: 15px;
  text-align: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
  /* stronger shadow */
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.registrant-card:hover {
  transform: translateY(-5px);
  /* lift on hover */
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
}

.profile-pic {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 10px;
  display: block;
  margin-left: auto;
  margin-right: auto;
}

.registrant-card p {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: #000;
  /* clear black text */
}

/* Counter Badge */
.count-badge {
  background-color: #374151;
  color: white;
  font-weight: bold;
  padding: 0.15rem 0.9rem;
  border-radius: 9999px;
  font-size: 0.9rem;
}

/* Post Career CSS */
.career-popup-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.5);
  z-index: 50;
}

.career-popup {
  background: #f9fafb;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  width: 600px;
  padding: 24px;
  position: relative;
}

.career-popup-close {
  position: absolute;
  top: 10px;
  right: 12px;
  background: none;
  border: none;
  font-size: 18px;
  color: #555;
  cursor: pointer;
}

.career-popup-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.career-popup-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.career-input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  margin-bottom: 15px;
  font-size: 14px;
  background: #ffffff;
  color: #111827;
}

.career-save-btn {
  background: #374151;
  color: white;
  font-size: 14px;
  font-weight: 500;
  padding: 10px;
  border-radius: 6px;
  width: 100%;
  border: none;
  cursor: pointer;
}

.career-save-btn:hover {
  background: #1f2937;
}

/* Posting Botton */
.plus-btn-text {
  background: none;
  border: none;
  font-size: 1.5rem;
  font-weight: bold;
  line-height: 1;
  color: #374151;
  cursor: pointer;
  transition: color 0.2s;
}

.plus-btn-text:hover {
  color: #000000;
}

/* Career Details */

.career-details-modal {
  background: #fff;
  padding: 2rem;
  border-radius: 1rem;
  width: 480px;
  max-width: 90%;
  position: relative;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  animation: fadeIn 0.25s ease;
  z-index: 2100;
  /* ensure above other overlays */
}

.career-info {
  margin: 0.4rem 0;
  color: #333;
}

.career-description {
  margin-top: 1rem;
  color: #555;
  line-height: 1.5;
}

.career-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1.25rem;
}

.btn-view-applicants {
  background-color: #374151;
  /* dark gray */
  color: #ffffff;
  /* white text */
  border: none;
  padding: 0.55rem 1.25rem;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.1s ease;
  display: inline-block;
  text-align: center;
}

.btn-view-applicants:hover {
  background-color: #4b5563;
  /* slightly lighter gray on hover */
  transform: scale(1.02);
}

/* Smooth appear animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Animate position when sidebar opens */
.hamburger {
  position: fixed;
  top: 15px;
  left: 18px;
  width: 25px;
  height: 18px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: none;
  border: none;
  cursor: pointer;
  z-index: 2000;
  /* ← raised from 100 to 2000 */
  padding: 0;
  transition: transform 0.6s ease;
  /* smoother animation */
}

/* Hamburger lines */
.hamburger span {
  display: block;
  height: 3px;
  width: 100%;
  background-color: white;
  border-radius: 2px;
}

/* When sidebar is open, move hamburger to the right */
.hamburger.shifted {
  transform: translateX(140px);
  /* Adjust this to your sidebar width */
}

/* Calendar for Deadline of Submission */
.deadline-input-wrapper {
  position: relative;
}

.deadline-input-wrapper input[type="date"] {
  width: 100%;
  padding: 10px 40px 10px 10px;
  /* space for icon */
  border: 1px solid #ccc;
  border-radius: 6px;
  background: #fff;
  color: #000;
  font-size: 14px;
}

/* Hide default calendar icon (browser default) */
.deadline-input-wrapper input[type="date"]::-webkit-calendar-picker-indicator {
  opacity: 0;
  position: absolute;
  right: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

/* Calendar icon position */
:deep(.deadline-input-wrapper .calendar-icon) {
  position: absolute;
  right: 12px;
  top: 37%;
  transform: translateY(-50%);
  pointer-events: none;
  display: flex;
  align-items: center;
  z-index: 2;
}

/* Applicants Modal */
.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.registrants-table-container {
  /* Ensures table fits on smaller screens if needed */
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead th {
  text-align: left;
  padding: 12px 15px;
  color: #777;
  font-size: 0.85rem;
  text-transform: uppercase;
  font-weight: 600;
  border-bottom: 1px solid #eee;
}

tbody tr {
  border-bottom: 1px solid #f5f5f5;
  /* Light separator line */
}

tbody td {
  padding: 15px 15px;
  color: #333;
  font-size: 0.95rem;
  /* Vertically aligns content in the middle */
  vertical-align: middle;
}

/* Status Colors (like in your image) */
.status-for-review {
  color: #ffa600;
  /* Green */
  font-weight: 500;
}

.status-scheduled {
  color: #4CAF50;
  /* Green */
  font-weight: 500;
}

.status-hired {
  color: #374151;
  /* Green */
  font-weight: 500;
}

.status-rejected {
  color: #d30707;
  /* Blue */
  font-weight: 500;
}

/* Base Schedule Button */
.schedule-btn {
  background-color: #334155;
  color: #fff;
  border: none;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.schedule-btn:hover {
  background-color: #1e293b;
  transform: scale(1.03);
}

.schedule-btn:disabled {
  background-color: #cbd5e1;
  color: #64748b;
  cursor: not-allowed;
  transform: none;
}

/* View Schedule Style */
.schedule-btn.view {
  background-color: #334155;
}

.schedule-btn.view:hover {
  background-color: #1e293b;
}

.schedule-btn.view:hover:not(:disabled) {
  background-color: #1e293b;
}

.dimmed {
  background-color: #22c55e;
  opacity: 0.7;
  cursor: not-allowed;
}

.blurred {
  filter: blur(1px) brightness(0.85);
  opacity: 0.6;
  pointer-events: none;
}

/* Rejected Applicants - Blur Effect */
.status-rejected+.interview-col .schedule-btn {
  filter: blur(1.2px) brightness(0.85);
  opacity: 0.6;
  pointer-events: none;
}

.interview-info {
  margin-top: 6px;
}

.interview-date {
  font-weight: 500;
  color: #374151;
}

.interview-time {
  font-size: 0.85rem;
  color: #6b7280;
}

/* View Schedule Modal */
.schedule-view p {
  margin: 8px 0;
  color: #111827;
}

.schedule-view a {
  color: #6d6d6d;
  text-decoration: none;
}

.schedule-view a:hover {
  text-decoration: underline;
}

/* Interview Modal Form */
.schedule-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-group label {
  font-weight: 500;
  margin-bottom: 4px;
  display: block;
}

.form-group label {
  font-weight: 500;
  margin-bottom: 4px;
  display: block;
}

input[type="datetime-local"],
input[type="text"] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 0.9rem;
}

.mode-group {
  display: flex;
  justify-content: space-around;
}

.schedule-confirm-btn {
  background-color: #334155;
  color: #fff;
  border: none;
  border-radius: 20px;
  padding: 8px 16px;
  cursor: pointer;
  margin-top: 10px;
  align-self: center;
  font-weight: 500;
  transition: all 0.2s ease;
}

.schedule-confirm-btn:hover {
  background-color: #1e293b;
}

/* Schedule Modal and View Schedule Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-box {
  background: #fff;
  border-radius: 12px;
  padding: 18px 22px;
  /* slightly reduced padding */
  width: 100%;
  max-width: 400px;
  /* narrower modal */
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
  position: relative;
  animation: fadeIn 0.2s ease-in-out;
}

.modal-box h3 {
  font-size: 1.2rem;
  margin-bottom: 10px;
  /* tighter title spacing */
  color: #222;
  text-align: center;
}

.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
}

.date-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.calendar-icon {
  position: absolute;
  right: 10px;
  top: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.calendar-icon svg {
  fill: black;
  pointer-events: none;
}

.schedule-form label {
  display: block;
  font-weight: 500;
  margin: 6px 0 3px;
  /* tighter spacing */
  color: #000;
  /* ✅ labels are now black */
}

.schedule-form input[type="datetime-local"],
.schedule-form input[type="text"] {
  width: 100%;
  padding: 6px 9px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 0.9rem;
  margin-bottom: 8px;
  background: #fff;
  /* ✅ makes textboxes white */
  color: #000;
  /* ensures readable text */
}

.mode-selection {
  display: flex;
  gap: 12px;
  /* slightly reduced */
  margin: 6px 0;
  /* closer to inputs */
}

.mode-selection label {
  cursor: pointer;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 8px;
}

.confirm-btn {
  background: #334155;
  color: white;
  border: none;
  padding: 7px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.cancel-btn {
  background: #ddd;
  border: none;
  padding: 7px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.confirm-btn:hover {
  background: #1e293b;
}

.cancel-btn:hover {
  background: #bbb;
}

.view-schedule-info p {
  margin: 4px 0;
  /* slightly tighter spacing */
  font-size: 0.95rem;
  color: #333;
}

/* Adjust modal width and spacing */
.schedule-modal,
.view-schedule-modal {
  width: 360px;
  /* narrower modal */
  max-width: 90%;
  background: white;
  padding: 18px 22px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.input-with-icon {
  position: relative;
  display: inline-block;
  width: 100%;
}

.input-with-icon input {
  width: 100%;
  padding: 8px 38px 8px 10px;
  /* space for the icon on the right */
  border: 1px solid #ccc;
  border-radius: 6px;
  background-color: #fff;
  font-size: 0.9rem;
  color: #000;
  box-sizing: border-box;
  height: 38px;
}

/* ✅ perfectly center the icon */
.input-with-icon .calendar-icon {
  position: absolute;
  right: 10px;
  top: 42%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  /* allows clicking through the icon */
}

.input-with-icon svg {
  width: 18px;
  height: 18px;
  fill: #000;
  /* solid black */
  opacity: 1;
  /* make sure it’s not faded */
}

@keyframes fadeIn {
  from {
    transform: scale(0.95);
    opacity: 0;
  }

  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* Show more css */
.show-more-btn {
  background-color: #374151;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 4px 10px;
  /* smaller padding */
  font-size: 0.85rem;
  /* smaller text */
  margin: 6px auto 0;
  /* centered horizontally */
  display: block;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
}

.show-more-btn:hover {
  background-color: #1d222b;
}

.show-more-btn:active {
  transform: scale(0.97);
}
</style>
