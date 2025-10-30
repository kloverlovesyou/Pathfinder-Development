<template>
  <div class="organization-trainings">

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

        <div class="spacer"></div> <!-- pushes signout down -->
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

      <!-- ✅ Upcoming Trainings Section -->
      <section class="upcoming">
        <div class="flex items-center justify-between">
          <h2 class="section-title flex items-center gap-1">
            Upcoming Trainings
            <span class="count-badge">{{ sortedUpcomingTrainings.length }}</span>
          </h2>

          <button class="plus-btn-text" @click="openTrainingPopup">+</button>
        </div>

        <!-- ✅ Grid Layout -->
        <div class="trainings-grid">
          <div v-for="training in visibleUpcomingTrainings" :key="training.trainingID" class="training-card">
            <div class="training-right" @click="openTrainingDetails(training)">
              <h3 class="training-title">{{ training.title }}</h3>
              <p class="training-date">{{ formatSchedule(training.schedule) }}</p>
            </div>

            <!-- Menu -->
            <div class="menu">
              <div class="menu-icon" @click.stop="toggleUpcomingMenu(training.trainingID)">
                ⋮
              </div>
              <div v-if="openUpcomingMenu === training.trainingID" class="dropdown-menu" @click.stop>
                <ul>
                  <li @click="deleteTraining(training.trainingID)">Delete Training</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Show More Button -->
        <button v-if="sortedUpcomingTrainings.length > 4" class="show-more-btn"
          @click="showAllUpcoming = !showAllUpcoming">
          {{ showAllUpcoming ? 'Show Less' : 'Show More' }}
        </button>
      </section>

      <!-- ✅ Completed Trainings Section -->
      <section class="completed">
        <div class="flex items-center justify-between">
          <h2 class="section-title flex items-center gap-1">
            Completed Trainings
            <span class="count-badge">{{ sortedCompletedTrainings.length }}</span>
          </h2>
        </div>

        <!-- ✅ Grid Layout -->
        <div class="trainings-grid">
          <div v-for="training in visibleCompletedTrainings" :key="training.trainingID" class="training-card">
            <div class="training-right" @click="openTrainingDetails(training)">
              <h3 class="training-title">{{ training.title }}</h3>
              <p class="training-date">{{ formatSchedule(training.schedule) }}</p>
            </div>

            <!-- Menu -->
            <div class="menu">
              <div class="menu-icon" @click.stop="toggleCompletedMenu(training.trainingID)">
                ⋮
              </div>
              <div v-if="openCompletedMenu === training.trainingID" class="dropdown-menu" @click.stop>
                <ul>
                  <li @click="deleteTraining(training.trainingID)">Delete Training</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Show More Button -->
        <button v-if="sortedCompletedTrainings.length > 4" class="show-more-btn"
          @click="showAllCompleted = !showAllCompleted">
          {{ showAllCompleted ? 'Show Less' : 'Show More' }}
        </button>

      </section>

      <!-- Registrants Modal -->
      <div v-if="showRegistrantsModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
          <button class="modal-close-btn" @click="closeModal">✕</button>
          <h3 class="modal-title">Registrants for {{ selectedTraining.title }}</h3>

          <div class="registrants-table-container">
            <table>
              <thead>
                <tr>
                  <th>FULL NAME</th>
                  <th>REGISTRATION DATE</th>
                  <th>STATUS</th>
                  <th class="cert-col-header">CERTIFICATE</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="person in registrantsList" :key="person.id">
                  <td>
                    <p class="registrant-name">{{ person.name }}</p>
                  </td>
                  <td>
                    <p class="registration-date">{{ person.dateRegistered }}</p>
                  </td>
                  <td :class="{
                    'status-attended': person.status === 'Attended',
                    'status-registered': person.status === 'Registered',
                    'status-did-not-attend': person.status === 'Did not Attend'
                  }">
                    {{ person.status }}
                  </td>

                  <td>
                    <button class="action-btn"
                      :class="person.hasCertificate ? 'certificate-issued-btn' : 'issue-cert-btn'"
                      :disabled="person.hasCertificate" @click="openCertUploadModal(person)">
                      {{ person.hasCertificate ? 'Certificate Issued' : 'Issue Certificate' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Training Details Modal -->
      <div v-if="showTrainingDetailsModal" class="modal-overlay" @click.self="closeTrainingDetails">
        <div class="training-details-modal">
          <button class="modal-close-btn" @click="closeTrainingDetails">✕</button>

          <h3 class="modal-title">{{ selectedTraining.title }}</h3>
          <p class="training-info">
            <strong>Description:</strong> {{ selectedTraining.description }}
          </p>
          <p class="training-info">
            <strong>Date and Time:</strong> {{ formatSchedule(selectedTraining.schedule) }}
          </p>
          <p class="training-info">
            <strong>Mode:</strong> {{ selectedTraining.mode }}
          </p>

          <!-- ✅ Show only if training is On-Site -->
          <p class="training-info" v-if="selectedTraining.mode === 'On-Site'">
            <strong>Location:</strong> {{ selectedTraining.location }}
          </p>

          <!-- ✅ Show only if training is Online -->
          <p class="training-info" v-if="selectedTraining.mode === 'Online'">
            <strong>Training Link: </strong>
            <a :href="selectedTraining.trainingLink" target="_blank" class="training-link">
              {{ selectedTraining.trainingLink }}
            </a>
          </p>

          <div class="training-actions">
            <button class="btn-view-registrants" @click="handleViewRegistrants(selectedTraining)">
              View Registrants
            </button>
          </div>
          <!-- ✅ Show QR only if training is live/upcoming within allowed time -->
          <div
            v-if="qrCodeValue && activeTrainingId === selectedTraining.trainingID && isTrainingActive(selectedTraining)"
            class="qr-container">
            <h3>QR Code (Expires at: {{ qrExpiresAt }})</h3>
            <qrcode-vue :value="qrCodeValue" :size="200" />
          </div>
        </div>
      </div>

      <!-- Certificate Upload -->
      <div v-if="showCertUploadModal" class="modal-overlay" @click.self="closeCertUploadModal">
        <div class="certificate-modal">

          <button class="cert-close-btn" @click="dismissModal">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M18 6L6 18M6 6L18 18" stroke="#4a4a4a" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </button>

          <div class="cert-modal-header">
            <div class="profile-avatar-wrapper">
              <img :src="selectedRegistrant.img" alt="Registrant Profile" class="profile-avatar-img"
                v-if="selectedRegistrant.img" />
              <div class="profile-avatar-placeholder" v-else>
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="7" r="4" fill="#a7a7a7" />
                  <path d="M17.5 19.5c0-3.314-2.239-6-5-6s-5 2.686-5 6h10z" fill="#a7a7a7" />
                </svg>
              </div>
            </div>
            <p class="registrant-name">{{ selectedRegistrant.name }}</p>
          </div>

          <p class="status">
            Status: {{ selectedRegistrant.status }}
          </p>

          <p class="date-registered">
            Date Registered: {{ selectedRegistrant.dateRegistered }}
          </p>

          <form @submit.prevent="sendCertificateDetails" class="cert-form">
            <input type="text" v-model="selectedRegistrant.certificateTrackingID" placeholder="Certificate Tracking ID"
              class="cert-input" required />

            <div class="cert-input-wrapper">
              <input type="date" v-model="selectedRegistrant.certificateGivenDate" placeholder="dd/mm/yyyy"
                class="cert-input date-input" required />

              <span class="calendar-icon">
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

            <div class="cert-input-wrapper file-input-wrapper">
              <input type="file" id="certificate-upload" @change="handleFileUpload" class="hidden-file-input" />
              <label for="certificate-upload" class="cert-input upload-label">
                {{ selectedRegistrant.uploadedFile ? selectedRegistrant.uploadedFile.name : 'Upload File' }}
              </label>
              <span class="upload-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M12 20L11.4 20.8L12 21.25L12.6 20.8L12 20ZM13 8C13 7.44772 12.5523 7 12 7C11.4477 7 11 7.44772 11 8H13ZM8 17L7.4 17.8L11.4 20.8L12 20L12.6 19.2L8.6 16.2L8 17ZM12 20L12.6 20.8L16.6 17.8L16 17L15.4 16.2L11.4 19.2L12 20ZM12 20H13V8H12H11V20H12Z"
                    fill="#4a4a4a" />
                  <path
                    d="M8 13C7.07003 13 6.60504 13 6.22354 12.8978C5.18827 12.6204 4.37962 11.8117 4.10222 10.7765C4 10.395 4 9.92997 4 9V8C4 6.13077 4 5.19615 4.40192 4.5C4.66523 4.04394 5.04394 3.66523 5.5 3.40192C6.19615 3 7.13077 3 9 3H15C16.8692 3 17.8038 3 18.5 3.40192C18.9561 3.66523 19.3348 4.04394 19.5981 4.5C20 5.19615 20 6.13077 20 8V9C20 9.92997 20 10.395 19.8978 10.7765C19.6204 11.8117 18.8117 12.6204 17.7765 12.8978C17.395 13 16.93 13 16 13"
                    stroke="#4a4a4a" stroke-width="2" stroke-linecap="round" />
                </svg>
              </span>
            </div>

            <button type="submit" class="cert-send-btn">Send</button>
          </form>
        </div>
      </div>

      <!-- Training Popup Modal -->
      <div v-if="showTrainingPopup" class="training-popup-overlay">
        <div class="training-popup">
          <!-- Close Button -->
          <button @click="closeTrainingPopup" class="training-popup-close">
            ✕
          </button>

          <!-- Title -->
          <h2 class="training-popup-title">Post Training</h2>

          <!-- Form -->
          <form @submit.prevent="saveTraining" class="training-popup-form">
            <input v-model="newTraining.title" type="text" placeholder="Title" class="training-input" />
            <textarea v-model="newTraining.description" placeholder="Description" class="training-input"></textarea>


            <!-- Schedule -->
            <div class="popup-form-group schedule-group">
              <label for="schedule">Schedule</label>
              <div class="schedule-input-wrapper">
                <!-- Date input with calendar icon -->
                <div class="date-input-wrapper">
                  <input type="date" id="schedule" v-model="newTraining.date" :min="todayDate" placeholder="Schedule" />
                  <span class="calendar-icon">
                    <!-- your SVG calendar icon -->
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

                <!-- Time -->
                <div class="time-input-wrapper">
                  <label for="time">Time</label>
                  <div class="date-input-wrapper">
                    <input type="time" id="time" v-model="newTraining.time" placeholder="Time" />
                    <span class="calendar-icon">
                      <!-- your SVG clock icon -->
                      <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                          d="M13 2.16663C7.02012 2.16663 2.16669 7.02006 2.16669 13C2.16669 18.9799 7.02012 23.8333 13 23.8333C18.9799 23.8333 23.8334 18.9799 23.8334 13C23.8334 7.02006 18.9799 2.16663 13 2.16663ZM13 21.6666C8.10012 21.6666 4.33335 17.8999 4.33335 13C4.33335 8.10006 8.10012 4.33329 13 4.33329C17.9 4.33329 21.6667 8.10006 21.6667 13C21.6667 17.8999 17.9 21.6666 13 21.6666Z"
                          fill="black" />
                        <path d="M13.8125 7.58337H12.1875V13.4067L16.9583 16.25L17.875 14.8334L13.8125 12.25V7.58337Z"
                          fill="black" />
                      </svg>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- On-Site / Online -->
            <div class="training-radio-group">
              <label>
                <input type="radio" value="On-Site" v-model="newTraining.mode" />
                On-Site
              </label>
              <label>
                <input type="radio" value="Online" v-model="newTraining.mode" />
                Online
              </label>
            </div>

            <!-- Conditional field -->
            <input v-if="newTraining.mode === 'On-Site'" v-model="newTraining.location" type="text"
              placeholder="Location" class="training-input" />

            <input v-else-if="newTraining.mode === 'Online'" v-model="newTraining.trainingLink" type="url"
              placeholder="Training Link" class="training-input" />

            <!-- Save -->
            <button type="submit" class="training-post-btn">Post</button>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import dictLogo from "@/assets/images/DICT-Logo-icon_only (1).png";
import axios from "axios";
import QrcodeVue from "qrcode.vue";

export default {
  components: { QrcodeVue }, // ✅ register component
  data() {
    return {
      dictLogo,
      showAllUpcoming: false,
      showAllCompleted: false,
      qrCodeValue: null,
      qrExpiresAt: null,
      activeTrainingId: null, // which training shows the QR
      qrExpireTimeout: null, // to clear old QR timers

      /* ==========================
         ✅ Dropdown Menu States
      ========================== */
      openUpcomingMenu: null,
      openCompletedMenu: null,

      showTrainingDetailsModal: false,
      selectedTraining: {},

      registrantsList: [], // removed hardcoded list, fetch from DB

      showRegistrantsModal: false,
      showCertUploadModal: false,

      selectedRegistrant: {
        name: "",
        dateRegistered: "",
        img: "",
        certificateTrackingID: "",
        certificateGivenDate: "",
        uploadedFile: null,
      },

      upcomingtrainings: [],
      completedtrainings: [],

      // Popup state + form
      showTrainingPopup: false,
      newTraining: {
        title: "",
        description: "",
        date: "",
        time: "",
        mode: "",
        location: "",
        trainingLink: "",
      },

      QrcodeVue: "",
      qrExpiresAt: "",
      activeTrainingId: null // which training shows the QR
    };
  },

  methods: {
    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    },

    handleViewRegistrants(training) {
      if (!training) {
        console.error("❌ handleViewRegistrants called without training");
        return;
      }
      this.openRegistrantsModal(training);
    },

    /* ==========================
       ✅ Dropdown Menu Logic
    ========================== */
    toggleUpcomingMenu(id) {
      this.openUpcomingMenu = this.openUpcomingMenu === id ? null : id;
      this.openCompletedMenu = null;
    },
    closeModal() {
      this.showRegistrantsModal = false;
    },
    toggleCompletedMenu(id) {
      this.openCompletedMenu = this.openCompletedMenu === id ? null : id;
      this.openUpcomingMenu = null;
    },

    closeAllMenus() {
      this.openUpcomingMenu = null;
      this.openCompletedMenu = null;
    },

    scheduleQR(training) {
      // If QR already active for this training, do nothing
      if (this.activeTrainingId === training.trainingID && this.qrCodeValue) return;

      const now = new Date();
      const trainingTime = new Date(training.schedule);
      const msUntilStart = trainingTime - now;

      if (msUntilStart <= 0) {
        // Already started or past, generate immediately
        this.generateQR(training);
      } else {
        setTimeout(() => this.generateQR(training), msUntilStart);
        console.log(`QR for "${training.title}" will generate in ${msUntilStart / 1000}s`);
      }
    },


    handleOutsideClick(e) {
      if (!e.target.closest(".menu")) {
        this.closeAllMenus();
      }
    },


    // ✅ Generate QR and call backend


    /* ==========================
  ✅ Registrants Modal
========================== */
    async openRegistrantsModal(training) {
      try {
        // Set selected training
        this.selectedTraining = training;

        // Get token from localStorage
        const token = localStorage.getItem("token");
        if (!token) {
          console.error("No token found. Please log in first.");
          alert("You must log in to view registrants.");
          return;
        }

        // Fetch registrants from API
        const response = await axios.get(
          `http://127.0.0.1:8000/api/trainings/${training.trainingID}/registrants`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
          }
        );

        // Populate registrants list dynamically
        this.registrantsList = response.data;

        // Open the modal
        this.showRegistrantsModal = true;

        // Close any dropdown menus
        this.closeAllMenus();

      } catch (error) {
        if (error.response) {
          console.error("Error fetching registrants:", error.response.status, error.response.data);
          if (error.response.status === 401) {
            alert("Unauthorized. Please log in again.");
          } else if (error.response.status === 403) {
            alert("You don't have permission to view registrants for this training.");
          } else if (error.response.status === 404) {
            alert("Training not found or you don't have access to it.");
          } else {
            alert("Failed to fetch registrants. Please try again.");
          }
        } else {
          console.error("Network or other error:", error.message);
          alert("An error occurred while fetching registrants.");
        }
      }
    },

    closeRegistrantsModal() {
      this.showRegistrantsModal = false;
      this.registrantsList = [];
    },

    /* ==========================
       ✅ Certificate Upload Modal
    ========================== */
    openCertUploadModal(registrant) {
      this.selectedRegistrant = {
        ...registrant,
        certificateTrackingID: registrant.certificateTrackingID || "",
        certificateGivenDate: registrant.certificateGivenDate || "",
        uploadedFile: null,
      };
      this.showCertUploadModal = true;
      this.closeAllMenus();
    },

    closeCertUploadModal() {
      this.showCertUploadModal = false;
      this.selectedRegistrant = { name: "", dateRegistered: "", img: "" };
    },

    handleFileUpload(event) {
      this.selectedRegistrant.uploadedFile = event.target.files[0];
    },

    sendCertificateDetails() {
      console.log("Sending Certificate Details:", this.selectedRegistrant);
      this.closeCertUploadModal();
    },

    dismissModal() {
      this.showCertUploadModal = false;
      this.selectedRegistrant = null;
    },

    /* ==========================
       ✅ Training Details Modal
    ========================== */
    openTrainingDetails(training) {
      this.selectedTraining = training;
      this.showTrainingDetailsModal = true;
      this.closeAllMenus();
    },

    closeTrainingDetails() {
      this.showTrainingDetailsModal = false;
    },

    handleViewRegistrants(training) {
      this.closeTrainingDetails();
      this.openRegistrantsModal(training); // pass training to fetch registrants
    },

    /* ==========================
       ✅ Trainings Fetch
    ========================== */
    async fetchTrainings() {
      const response = await axios.get("http://127.0.0.1:8000/api/trainings");
      const newTrainings = response.data;

      newTrainings.forEach(training => {
        // Only schedule if this training isn’t in upcomingtrainings yet
        if (!this.upcomingtrainings.some(t => t.trainingID === training.trainingID)) {
          this.upcomingtrainings.push(training);
          this.scheduleQR(training);
        }
      });
    },


    /* ==========================
       ✅ Training Popup Methods
    ========================== */
    openTrainingPopup() {
      this.showTrainingPopup = true;
    },

    closeTrainingPopup() {
      this.showTrainingPopup = false;
      this.newTraining = {
        title: "",
        description: "",
        date: "",
        time: "",
        mode: "",
        location: "",
        trainingLink: "",
      };
    },

    async saveTraining() {
      try {
        if (!this.newTraining.date || !this.newTraining.time) {
          alert("PLEASE SELECT BOTH A DATE AND TIME FOR THE TRAINING");
          return;
        }

        // ✅ Combine date and time as a proper local timestamp (not UTC)
        const combinedSchedule = `${this.newTraining.date}T${this.newTraining.time}:00`;

        const payload = {
          title: this.newTraining.title,
          description: this.newTraining.description,
          // ✅ Store local time in the same format you see (not auto-converted to UTC)
          schedule: combinedSchedule,
          mode: this.newTraining.mode,
          location: this.newTraining.location || null,
          training_link: this.newTraining.trainingLink || null,
        };

        const token = localStorage.getItem("token");
        const response = await axios.post("http://127.0.0.1:8000/api/trainings", payload, {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
          },
        });

        if (response.data && response.data.data) {
          const newTraining = response.data.data;

          const storedUser = localStorage.getItem("user");
          let organizationName = "Unknown Organization";
          if (storedUser) {
            const user = JSON.parse(storedUser);
            organizationName = user.displayName || user.name || "Unknown Organization";
          }

          // ✅ Use local date and time directly without timezone shift
          const scheduleDate = new Date(combinedSchedule);
          const formattedDate = scheduleDate.toLocaleDateString("en-US", {
            weekday: "short",
            year: "numeric",
            month: "short",
            day: "numeric",
          });
          const formattedTime = scheduleDate.toLocaleTimeString("en-US", {
            hour: "2-digit",
            minute: "2-digit",
          });

          this.upcomingtrainings.push({
            id: newTraining.trainingID,
            title: newTraining.title,
            description: newTraining.description,
            schedule: combinedSchedule,
            date: formattedDate,
            time: formattedTime,
            mode: newTraining.mode,
            ...(newTraining.mode === "On-Site"
              ? { location: newTraining.location }
              : { trainingLink: newTraining.trainingLink }),
            organizationName,
          });
        }

        alert("TRAINING POSTED SUCCESSFULLY!!!");
        this.newTraining = { title: "", description: "", date: "", time: "", mode: "", location: "", trainingLink: "" };
        this.showTrainingPopup = false;
      } catch (error) {
        console.error("ERROR SAVING TRAINING:", error.response?.data || error);
        alert("SOMETHING WENT WRONG WHILE SAVING THE TRAINING");
      }
    },

    async deleteTraining(trainingID) {
      if (!confirm("Are you sure you want to delete this training?")) return;

      try {
        const token = localStorage.getItem("token");
        if (!token) {
          alert("You must be logged in to delete a training.");
          return;
        }

        // Send DELETE request to your backend
        await axios.delete(`http://127.0.0.1:8000/api/trainings/${trainingID}`, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        });

        // Remove deleted training from UI
        this.upcomingtrainings = this.upcomingtrainings.filter(t => t.trainingID !== trainingID);
        this.completedtrainings = this.completedtrainings.filter(t => t.trainingID !== trainingID);

        this.closeAllMenus();
        alert("✅ Training deleted successfully.");

      } catch (error) {
        if (error.response) {
          console.error("Delete error:", error.response.data);
          if (error.response.status === 404) {
            alert("Training not found or already deleted.");
          } else if (error.response.status === 401) {
            alert("Unauthorized. Please log in again.");
          } else {
            alert("Failed to delete training. Please try again.");
          }
        } else {
          console.error("Network error:", error.message);
          alert("Network error. Please check your connection.");
        }
      }
    },

    formatSchedule(schedule) {
      if (!schedule) return "No schedule set";
      try {
        // ✅ Prevent UTC conversion; parse as local
        const [datePart, timePart] = schedule.split(/[ T]/);
        const [year, month, day] = datePart.split("-");
        const [hour, minute, second] = (timePart || "00:00:00").split(":");

        const date = new Date(
          Number(year),
          Number(month) - 1,
          Number(day),
          Number(hour),
          Number(minute),
          Number(second || 0)
        );

        return date.toLocaleString("en-US", {
          weekday: "short",
          year: "numeric",
          month: "short",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
          hour12: true, // ✅ 12-hour format
        });
      } catch (error) {
        return schedule;
      }
    },

    /* ✅ ADD THIS FUNCTION HERE */
    async generateQR(training) {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.post(
          "http://127.0.0.1:8000/api/trainings/generate-qr",
          { trainingID: training.trainingID },
          { headers: { Authorization: `Bearer ${token}` } }
        );

        // Assign QR data
        this.qrCodeValue = `http://127.0.0.1:8000/api/attendance/checkin?trainingID=${training.trainingID}&key=${response.data.key}`;
        this.qrExpiresAt = new Date(response.data.expires_at); // make it a Date object
        this.activeTrainingId = training.trainingID;

        console.log(`✅ QR Generated for "${training.title}", expires at ${this.qrExpiresAt}`);

        // Clear previous timer if any
        if (this.qrExpireTimeout) clearTimeout(this.qrExpireTimeout);

        // Calculate remaining time until expiration
        const now = new Date();
        const msUntilExpire = this.qrExpiresAt - now;

        if (msUntilExpire > 0) {
          this.qrExpireTimeout = setTimeout(() => {
            this.qrCodeValue = null;
            this.qrExpiresAt = null;
            this.activeTrainingId = null;
            console.log(`QR for "${training.title}" expired.`);
          }, msUntilExpire);
        } else {
          // Already expired
          this.qrCodeValue = null;
          this.qrExpiresAt = null;
          this.activeTrainingId = null;
          console.log(`QR for "${training.title}" already expired.`);
        }

      } catch (error) {
        console.error("QR GENERATION FAILED:", error);
        alert("Failed to generate QR");
      }
    },


  },


  mounted() {
    this.fetchTrainings();
    document.addEventListener("click", this.handleOutsideClick);
  },

  beforeUnmount() {
    document.removeEventListener("click", this.handleOutsideClick);
  },

  computed: {
    todayDate() {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, "0");
      const day = String(today.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`; // format: YYYY-MM-DD
    },
    visibleUpcomingTrainings() {
      const list = this.sortedUpcomingTrainings;
      return this.showAllUpcoming ? list : list.slice(0, 4);
    },

    visibleCompletedTrainings() {
      const list = this.sortedCompletedTrainings;
      return this.showAllCompleted ? list : list.slice(0, 4);
    },

    sortedUpcomingTrainings() {
      const now = new Date();
      return this.upcomingtrainings
        .filter(t => new Date(t.schedule) >= now)
        .sort((a, b) => new Date(a.schedule) - new Date(b.schedule));
    },

    sortedCompletedTrainings() {
      const now = new Date();
      return this.upcomingtrainings
        .filter(t => new Date(t.schedule) < now)
        .sort((a, b) => new Date(a.schedule) - new Date(b.schedule));
    },
  },
};
</script>


<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
const isSidebarOpen = ref(true);
const organizationName = ref("");
const now = ref(new Date());


// Toggle sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

const router = useRouter();
// Sidebar navigation functions
const goToProfile = () => router.push('/profile');
const goToHome = () => router.push('/organization');
const goToTrainings = () => router.push({ name: 'OrgTrainings' });
const goToCareers = () => router.push({ name: 'OrgCareers' });
const goToCalendar = () => router.push('/app/calendar');

// Generic navigation function
const navigateTo = (route) => {
  router.push(route);
}


function isTrainingActive(training) {
  const trainingDate = new Date(training.schedule);
  return now.value <= trainingDate;
}

setInterval(() => {
  now.value = new Date();
}, 60000); // every minute
// Get org name from localStorage on mount
onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    const user = JSON.parse(storedUser);
    if (user.role === "organization") {
      organizationName.value = user.displayName || user.name;
    }
  }
});

const logout = () => {
  localStorage.removeItem('user');
  localStorage.removeItem('token');
  router.push({ name: 'Login' });
}
</script>

<style scoped>
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

.organization-trainings {
  display: flex;
  height: 100vh;
  font-family: 'Poppins', sans-serif;
  background-color: #f4f4f4;
}

/* Sidebar */
.sidebar {
  width: 200px;
  /* Expanded width */
  background-color: #44576D;
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
  color: #44576D;
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
  background-color: #44576D;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
}

.applicants-btn {
  background-color: #44576D;
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

.training-slider {
  display: flex;
  flex-direction: column;
  /* ✅ vertical */
  overflow-y: auto;
  /* ✅ vertical scroll */
  max-height: 400px;
  /* adjust as needed */
  gap: 1rem;
  /* spacing between cards */
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

.trainings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  /* 4 per row if enough space */
  gap: 1rem;
}

.training-card {
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

.training-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.training-card.completed {
  opacity: 0.85;
  background-color: #f8f9fa;
}

.training-left {
  margin-right: 12px;
}

.training:last-child {
  border-bottom: none;
}

.training-title {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 4px;
  color: #2d3748;
}

.training-date {
  font-size: 13px;
  color: #4a5568;
}

.training-company {
  font-size: 14px;
  color: #718096;
  margin-bottom: 10px;
}

.edit-btn {
  background-color: #44576D;
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
.status-attended {
  color: #4CAF50;
  /* Green */
  font-weight: 500;
}

.status-did-not-attend {
  color: #d30707;
  /* Blue */
  font-weight: 500;
}

/* Specific Styles for Issue Certificate Button */
.issue-cert-btn {
  /* This is the primary action button, usually a distinct color */
  background-color: #374151;
  /* Example: A standard blue */
  color: white;
}

.issue-cert-btn-regonly {
  /* This is the primary action button, usually a distinct color */
  background-color: #ffffff;
  /* Example: A standard blue */
  color: rgb(105, 105, 105);
}

.issue-cert-btn:hover {
  background-color: #374151;
}

/* General Action Button Styles (ensures consistent size) */
.action-btn {
  /* Set a consistent minimum width for all buttons (fixes the sizing issue) */
  min-width: 150px;
  white-space: nowrap;
  /* Prevents text wrapping */

  padding: 8px 15px;
  border-radius: 4px;
  font-size: 0.85rem;
  cursor: pointer;
  border: none;
  transition: background-color 0.2s;
}

/* 'Certificate Issued' button (the disabled, lighter one) */
.certificate-issued-btn {
  background-color: #f0f0f0;
  color: #aaa;
  /* Lighter text color */
  cursor: not-allowed;
  pointer-events: none;
  /* Visually disabled */
}

.modal-overlay {
  /* Covers the entire viewport */
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  /* Semi-transparent dark background */
  background-color: rgba(0, 0, 0, 0.4);
  /* Ensures it's above the rest of the page content */
  z-index: 1000;
  /* Enables centering of the modal child */
  display: flex;
  align-items: center;
  /* Centers vertically */
  justify-content: center;
  /* Centers horizontally */
}

.modal-content {
  position: relative;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 700px;
  max-height: 85vh;
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

.registrants-grid {
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

/* Post Training CSS */
.training-popup-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.5);
  z-index: 50;
}

.training-popup {
  background: #f9fafb;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  width: 400px;
  padding: 24px;
  position: relative;
}

.training-popup-close {
  position: absolute;
  top: 10px;
  right: 12px;
  background: none;
  border: none;
  font-size: 18px;
  color: #555;
  cursor: pointer;
}

.training-popup-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.training-popup-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.training-input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  margin-bottom: 15px;
  font-size: 14px;
  background: #ffffff;
  color: #111827;
}

.training-post-btn {
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

.training-save-btn:hover {
  background: #1f2937;
}

/* Post Training CSS */
.training-popup-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.5);
  z-index: 50;
}

.training-popup {
  background: #f9fafb;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  width: 400px;
  padding: 24px;
  position: relative;
}

.training-popup-close {
  position: absolute;
  top: 10px;
  right: 12px;
  background: none;
  border: none;
  font-size: 18px;
  color: #555;
  cursor: pointer;
}

.training-popup-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
}

.training-popup-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.training-input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: #ffffff;
  color: #111827;
}

.training-radio-group {
  display: flex;
  gap: 20px;
  font-size: 14px;
  color: #374151;
}

.training-save-btn {
  background: #374151;
  color: white;
  font-size: 14px;
  font-weight: 500;
  padding: 10px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.training-save-btn:hover {
  background: #1f2937;
}

/* Schedule input styles */
.schedule-input-wrapper {
  position: relative;
}

.schedule-input-wrapper input[type="date"] {
  width: 100%;
  padding: 10px 40px 10px 10px;
  /* space for icon */
  border: 1px solid #ccc;
  border-radius: 6px;
  background: #fff;
  color: #000;
  /* input text black */
  font-size: 14px;
}

/* Hide default date picker icon (browser) */
.schedule-input-wrapper input[type="date"]::-webkit-calendar-picker-indicator {
  opacity: 0;
  position: absolute;
  right: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

/* Position calendar SVG */
.schedule-input-wrapper .calendar-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
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

/* Posting Botton */
.plus-btn-text {
  background: none;
  border: none;
  font-size: 1.5rem;
  /* same scale as heading */
  font-weight: bold;
  line-height: 1;
  /* keeps alignment neat */
  color: #374151;
  /* highlight color */
  cursor: pointer;
  transition: color 0.2s;
}

.plus-btn-text:hover {
  color: #000000;
}

/* Upload Certificate */

/* The new modal container for the specific content */
.certificate-modal {
  background: #fff;
  padding: 30px;
  /* Increase padding for more breathing room */
  border-radius: 8px;
  position: relative;
  width: 90%;
  max-width: 600px;
}

/* Close Button Styling */
.cert-close-btn {
  position: absolute;
  top: 15px;
  /* Distance from the top edge */
  right: 15px;
  /* Distance from the right edge */
  background: none;
  /* Ensures no button background */
  border: none;
  padding: 0;
  cursor: pointer;
  transition: opacity 0.2s;
  line-height: 1;
  z-index: 1002;
  /* Ensures it sits above form elements */
}

.cert-close-btn:hover {
  opacity: 0.7;
}

.cert-close-btn svg {
  width: 20px;
  height: 20px;
  display: block;
}

/* Modal Header - Profile Picture and Name */
.cert-modal-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 5px;
  /* Adjusted to give space below the close button */
  margin-bottom: 20px;
}


.profile-avatar-wrapper {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background-color: #E6E0E9;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  margin-bottom: 10px;
}

.registrant-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #4a4a4a;
  text-align: left;
}

/* Date Registered Text */
.date-registered {
  font-size: 0.9rem;
  color: #4a4a4a;
  margin-bottom: 10px;
  text-align: center;
}

/* Status Text */
.status {
  font-size: 0.9rem;
  color: #4a4a4a;
  margin-bottom: 2px;
  text-align: center;
}

/* Form Styling */
.cert-form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.cert-input {
  width: 100%;
  padding: 12px 15px;
  /* Base padding */
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #4a4a4a;
  box-sizing: border-box;
  outline: none;
  background-color: #FFFFFF;
  height: 44px;
  /* Explicit height ensures consistent vertical centering */
}

.cert-input-wrapper {
  position: relative;
  width: 100%;
  /* Remove 'display: flex' if it causes alignment issues with relative children.
       Keep it simple for absolute positioning to work correctly against the wrapper. */
}

.cert-input-wrapper .cert-input,
.cert-input-wrapper .upload-label {
  /* Push the input content to the left to make room for the icon (e.g., 15px for icon + 15px margin) */
  padding-right: 45px;
}

.cert-input:focus {
  border-color: #6a0dad;
}

.cert-input.date-input {
  /* Hide the default date picker icon provided by the browser */
  -webkit-appearance: none;
  appearance: none;
}

.cert-input.date-input::-webkit-calendar-picker-indicator {
  /* IMPORTANT: Make the hidden picker fully cover the input *and* be transparent so that the user's click still opens the calendar, 
       even when they click on the space occupied by the custom SVG. */
  opacity: 0;
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  cursor: pointer;
  /* Ensures the cursor changes across the whole input area */
  z-index: 5;
  /* Place it above the SVG but below the actual input content (if needed) */
}

.calendar-icon {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  right: 15px;

  /* REMOVE pointer-events: none; - The icon itself is now a visual guide, 
       but the transparent calendar-picker-indicator above it is what is truly clickable. 
       We add a cursor style here to visually reinforce it. */
  cursor: pointer;

  width: 20px;
  height: 20px;
}

.calendar-icon,
.upload-icon {
  position: absolute;
  /* Aligns vertically in the middle of the input wrapper */
  top: 50%;
  /* Moves the icon up by half its own height for perfect centering */
  transform: translateY(-50%);
  /* Puts the icon 15px inside the right edge of the wrapper/input */
  right: 15px;
  pointer-events: none;
  /* Allows clicks to pass through to the input */

  /* Ensure the icon size is appropriate (e.g., 20px) regardless of SVG default size */
  width: 20px;
  height: 20px;
  display: flex;
  /* Helps ensure the SVG inside fills the container */
  align-items: center;
  justify-content: center;
}

.calendar-icon svg,
.upload-icon svg {
  width: 100%;
  height: 100%;
  /* Optional: Change fill/stroke color for consistent appearance */
  fill: #4a4a4a;
  stroke: #4a4a4a;
}

/* Upload File Specific Styles */
.file-input-wrapper {
  cursor: pointer;
}

.hidden-file-input {
  position: absolute;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.upload-label {
  /* *** MODIFICATION: Ensure upload label background is white *** */
  background-color: #FFFFFF;
  border: 1px solid #ccc;
  color: #888;
  padding: 12px 15px;
  border-radius: 8px;
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* If a file is selected, use the standard text color */
.upload-label:not(:empty) {
  color: #4a4a4a;
}


/* Send Button */
.cert-send-btn {
  background-color: #597d9e;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  margin-top: 10px;
  align-self: flex-end;
  width: auto;
}

/* Training details */
.training-details-modal {
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

.training-info {
  margin: 0.4rem 0;
  color: #333;
}

.training-description {
  margin-top: 1rem;
  color: #555;
  line-height: 1.5;
}

.training-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1.25rem;
}

.btn-view-registrants {
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

.btn-view-registrants:hover {
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

/* Time Picker */
.input-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 12px;
}

.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}

.input-field {
  width: 100%;
  padding: 10px 36px 10px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}

.input-field:focus {
  border-color: #4c6ef5;
}


/* To fix the alignment of schedule and time */
/* ✅ Shared wrapper for date and time inputs */
.date-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.date-input-wrapper input[type="date"],
.date-input-wrapper input[type="time"] {
  width: 100%;
  padding: 10px 40px 10px 12px;
  /* extra right padding for the icon */
  border: 1px solid #ccc;
  border-radius: 8px;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  color: #333;
  outline: none;
  background-color: #fff;
  box-sizing: border-box;
}

.date-input-wrapper input[type="date"]:focus,
.date-input-wrapper input[type="time"]:focus {
  border-color: #4c6ef5;
}

/* ✅ Use same icon position for both schedule and time fields */
.calendar-icon,
.clock-icon {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  right: 15px;
  pointer-events: none;
  /* keep clicks working on the input */
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.calendar-icon svg,
.clock-icon svg {
  width: 100%;
  height: 100%;
  fill: #4a4a4a;
  stroke: #4a4a4a;
}

/* ✅ Hide native picker indicators (keeps the clean icon look) */
input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator {
  opacity: 0;
  position: absolute;
  right: 10px;
  width: 26px;
  height: 26px;
  cursor: pointer;
}
</style>
