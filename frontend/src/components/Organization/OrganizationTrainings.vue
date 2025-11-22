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
              <div class="action" @click="navigateTo({ name: 'OrgUpdateProfile' })">
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

      <!-- ✅ GLOBAL SEARCH -->
      <section class="global-search-section">
        <div class="flex justify-center my-6 px-4">
          <input type="text" v-model="globalSearchQuery" placeholder="Search trainings..."
            class="global-search-bar text-black px-4 py-2 border rounded-lg w-full sm:w-3/4 md:w-1/2 lg:w-1/3" />
        </div>
      </section>

      <!-- ✅ Upcoming Trainings Section -->
      <section class="upcoming">
        <div class="flex items-center justify-between">
          <h2 class="section-title flex items-center gap-1">
            Upcoming Trainings
            <span class="count-badge">{{ sortedUpcomingTrainings.length }}</span>
          </h2>

          <button class="plus-btn-text" @click="openTrainingPopup()">+</button>
        </div>

        <!-- ✅ Grid Layout -->
        <div class="trainings-grid">
          <div v-for="training in visibleFilteredUpcoming" :key="training.trainingID" class="training-card"
            @click="openTrainingDetails(training)">
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
                  <li @click="updateTraining(training.trainingID)">Update Training</li>
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
          <div v-for="training in visibleFilteredCompleted" :key="training.trainingID" class="training-card"
            @click="openTrainingDetails(training)">
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
                  <th><input type="checkbox" v-model="selectAll" @change="toggleSelectAll"></th>
                  <th>CERTIFICATE TRACKING ID</th>
                  <th>FULL NAME</th>
                  <th>REGISTRATION DATE</th>
                  <th>STATUS</th>
                  <th class="cert-col-header">CERTIFICATE</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="person in registrantsList" :key="person.id">
                  <td>
                    <input type="checkbox" v-model="person.selected">
                  </td>
                  <td>
                    <p class="registrant-certid">{{ person.id }}</p>
                  </td>
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
                            :disabled="person.hasCertificate"
                            @click="issueCertificate(person)">
                      {{ person.hasCertificate ? 'Certificate Issued' : 'Issue Certificate' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Issue Certificates To All Button -->
          <div class="modal-footer">
            <button class="bulk-issue-btn" @click="issueCertificatesToSelected">
              Issue Certificates to Selected
            </button>
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
            <strong>Date and Start Time:</strong> {{ formatSchedule(selectedTraining.schedule) }}
          </p>
          <p class="training-info">
            <strong>End Time:</strong> {{ formatSchedule(selectedTraining.end_time) }}
          </p> <!-- 👈 Show end_time -->
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
            v-if="activeTrainingQR && activeTrainingId === selectedTraining.trainingID && isTrainingActive(selectedTraining)"
            class="qr-container">
            <h3>QR Code (Expires at: {{ activeTrainingQRExpiresAt }})</h3>
            <qrcode-vue :value="activeTrainingQR" :size="200" />
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
          <h2 class="training-popup-title">{{ isEditMode ? "Update Training" : "Post Training" }}</h2>

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

                <!-- Start Time -->
                <div class="time-input-wrapper">
                  <label for="startTime">Start Time</label>
                  <div class="date-input-wrapper">
                    <input type="time" id="startTime" v-model="newTraining.startTime" placeholder="Start Time" />
                    <span class="calendar-icon">
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

                <!-- End Time -->
                <div class="time-input-wrapper">
                  <label for="endTime">End Time</label>
                  <div class="date-input-wrapper">
                    <input type="time" id="endTime" v-model="newTraining.endTime" placeholder="End Time" />
                    <span class="calendar-icon">
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

            <!-- Tag selection -->
            <div class="relative mb-4">
              <label class="block font-semibold text-gray-600 mb-2">Tags</label>
              <!-- Tag List Wrapper for horizontal scrolling -->
              <div class="tag-list-wrapper">
                <div class="tag-list">
                  <span v-for="tag in tagOptions" :key="tag.TagID" class="tag-chip" @click="toggleTag(tag.TagID)"
                    :class="{ 'tag-chip-selected': isTagSelected(tag.TagID) }">
                    {{ tag.TagName }}
                  </span>
                </div>
              </div>
              <!-- Display Selected Tags as Chips -->
              <div v-if="newTraining.Tags.length" class="flex flex-wrap gap-2 mt-2">
                <span v-for="tagID in newTraining.Tags" :key="tagID"
                  class="bg-blue-100 text-blue-700 text-sm px-3 py-1 rounded-full">
                  {{ getTagName(tagID) }}
                  <button @click.stop="removeTag(tagID)" class="ml-1 text-red-500">×</button> <!-- Remove Button -->
                </span>
              </div>
              <!-- Input for New Tag -->
              <input v-model="newTagName" type="text" placeholder="Add new tag" class="training-input mt-2" />
              <button @click.prevent="addTag" class="training-save-btn mt-2">Add Tag</button>
            </div>
            <!-- Save -->
            <button type="submit" class="training-post-btn">{{ isEditMode ? "Update" : "Post" }}</button>
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
import api from "@/composables/api.js";
import { activeTrainingQR, activeTrainingId, scheduleQR } from "@/composables/useTrainingQR.js";
import { uploadCertificate } from "@/lib/supabase.js";
import jsPDF from "jspdf";


export default {
  components: { QrcodeVue }, // ✅ register component
  data() {
    return {
      dictLogo,
      globalSearchQuery: '',
      showAllUpcoming: false,
      showAllCompleted: false,
      activeTrainingQR,  // <-- QR code value (reactive)
      activeTrainingId,  // <-- which training is active
      isEditMode: false,
      trainingToEditId: null,
      qrCodeValue: null,
      qrExpiresAt: null,
      activeTrainingId: null, // which training shows the QR
      selectAll: false,

      showBulkCertModal: false,
      certificateData: {
        certTrackingID: '',
        certGivenDate: '',
        file: null,
      },

      
      selectedRegistrant: null,
      bulkCertData: {
        baseTrackingID: '',
        certGivenDate: '',
        certificates: []
      },

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
        startTime: "",
        endTime: "",
        mode: "",
        location: "",
        trainingLink: "",
        Tags: []
      },

      QrcodeVue: "",
      qrExpiresAt: "",
      activeTrainingId: null, // which training shows the QR
      tagOptions: [],
      newTagName: ''
    };
  },

  methods: {


  async issueCertificate(person) {
    try {
      if (person.hasCertificate) return; // already issued

      const givenDate = new Date().toISOString().split("T")[0];

      // Generate PDF in landscape
      const doc = new jsPDF({ orientation: "landscape", unit: "pt", format: "a4" });

      const pageWidth = doc.internal.pageSize.getWidth();
      const pageHeight = doc.internal.pageSize.getHeight();

      // Vertical spacing
      const lines = [
        { text: "Certificate of Completion", size: 28 },
        { text: `This is to certify that ${person.name}`, size: 22 },
        { text: `has completed the training: ${this.selectedTraining.title}`, size: 18 },
        { text: `Certificate Tracking ID: ${person.id}`, size: 14 },
        { text: `Date Issued: ${givenDate}`, size: 14 }
      ];

      // Total height of all lines for centering
      const totalHeight = lines.reduce((sum, line) => sum + line.size + 10, 0); // 10pt spacing between lines
      let startY = (pageHeight - totalHeight) / 2; // starting Y to center vertically

      lines.forEach(line => {
        doc.setFontSize(line.size);
        doc.text(line.text, pageWidth / 2, startY, null, null, "center");
        startY += line.size + 10; // move to next line
      });

      const pdfBlob = doc.output("blob");

      // Use name as filename (sanitized)
      const safeName = person.name.replace(/[/\\?%*:|"<>]/g, "_");
      const pdfFile = new File([pdfBlob], `${safeName}.pdf`, { type: "application/pdf" });

      // Upload PDF to Supabase
      const filePath = await uploadCertificate(pdfFile);
      if (!filePath) throw new Error("Failed to upload certificate");
      
      console.log("Certificate public URL:", filePath);

      // Send metadata to backend
      const token = localStorage.getItem("token");
      const formData = new FormData();
      formData.append("certificateTrackingID", person.id); // Use registrationID
      formData.append("certificateGivenDate", givenDate);
      formData.append("certificateFilePath", filePath);

      await axios.put(
        `${import.meta.env.VITE_API_BASE_URL}/registrations/${person.id}/certificate`,
        formData,
        { headers: { Authorization: `Bearer ${token}`, "Content-Type": "multipart/form-data" } }
      );

      // Update UI
      person.hasCertificate = true;
      person.certificateTrackingID = person.id;

      alert(`Certificate issued for ${person.name}!`);
    } catch (error) {
      console.error("Error issuing certificate:", error);
      alert(`Failed to issue certificate for ${person.name}.`);
    }
  },
    async issueBulkCertificates(selectedRegistrants) {
    try {
      const baseID = `CERT-${Date.now()}`;
      const givenDate = new Date().toISOString().split("T")[0];

      const certificates = await Promise.all(
        selectedRegistrants.map(async (person) => {
          const doc = new jsPDF();
          doc.setFontSize(20);
          doc.text("Certificate of Completion", 105, 40, null, null, "center");
          doc.setFontSize(14);
          doc.text(`This is to certify that ${person.name}`, 105, 60, null, null, "center");
          doc.text(`has completed the training: ${this.selectedTraining.title}`, 105, 70, null, null, "center");

          const pdfBlob = doc.output("blob");
          const pdfFile = new File([pdfBlob], `${person.name}.pdf`, { type: "application/pdf" });
          const filePath = await uploadCertificate(pdfFile);

          return { filePath };
        })
      );

      const token = localStorage.getItem("token");
      await axios.put(
        `${import.meta.env.VITE_API_BASE_URL}/registrations/${this.selectedTraining.id}/bulk-certificates`,
        { baseTrackingID: baseID, certGivenDate: givenDate, certificates },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      alert("Bulk certificates issued successfully!");
    } catch (error) {
      console.error("Error issuing bulk certificates:", error);
      alert("Failed to issue bulk certificates.");
    }
  },

  async issueCertificatesToSelected() {
    const selectedPeople = this.registrantsList.filter(p => p.selected && !p.hasCertificate);
    if (!selectedPeople.length) return alert("No selected registrants or all already issued.");

    try {
      const certGivenDate = new Date().toISOString().split("T")[0];
      const baseTrackingID = `CERT-${Date.now()}`; // ✅ Base tracking ID for bulk issuance
      const certificates = [];

      for (const person of selectedPeople) {
        const doc = new jsPDF();
        doc.setFontSize(20);
        doc.text("Certificate of Completion", 105, 40, null, null, "center");
        doc.setFontSize(14);
        doc.text(`This is to certify that ${person.name}`, 105, 60, null, null, "center");
        doc.text(`has completed the training: ${this.selectedTraining.title}`, 105, 70, null, null, "center");
        doc.text(`Certificate Tracking ID: ${person.id}`, 105, 90, null, null, "center");
        doc.text(`Date Issued: ${certGivenDate}`, 105, 100, null, null, "center");

        const pdfBlob = doc.output("blob");
        const pdfFile = new File([pdfBlob], `${person.id}.pdf`, { type: "application/pdf" });

        const filePath = await uploadCertificate(pdfFile);
        if (!filePath) throw new Error(`Failed to upload certificate for ${person.name}`);

        certificates.push({ filePath });
      }

      const token = localStorage.getItem("token");
      await axios.post(
        `${import.meta.env.VITE_API_BASE_URL}/trainings/${this.selectedTraining.trainingID}/certificates/bulk`,
        { baseTrackingID, certGivenDate, certificates },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      // Update UI
      selectedPeople.forEach(p => {
        p.hasCertificate = true;
        p.certificateTrackingID = p.id; // Registration ID as tracking ID
      });

      alert("Certificates issued successfully!");
    } catch (error) {
      console.error("Bulk issuance error:", error);
      alert("Failed to issue certificates.");
    }
  },

  toggleSelectAll() {
    this.registrantsList.forEach(person => {
      person.selected = this.selectAll;
    });
  },

   updateSelectAll() {
    this.selectAll = this.registrantsList.every(person => person.selected);
  },
    openBulkCertModal() {
      const registrantsWithoutCert = this.registrantsList.filter(r => !r.hasCertificate);
      if (registrantsWithoutCert.length === 0) {
        alert("All registrants already have certificates.");
        return;
      }
      this.bulkCertData = {
        baseTrackingID: '',
        certGivenDate: '',
        certificates: new Array(registrantsWithoutCert.length).fill(null)
      };
      this.showBulkCertModal = true;
    },

    closeBulkCertModal() {
      this.showBulkCertModal = false;
      this.bulkCertData = {
        baseTrackingID: '',
        certGivenDate: '',
        certificates: []
      };
    },

    handleBulkFileUpload(event, index) {
      this.bulkCertData.certificates[index] = event.target.files[0];
      this.$forceUpdate(); // Force reactivity update
    },

    async sendBulkCertificates() {
      const token = localStorage.getItem("token");
      if (!token) {
        alert("Please log in to continue.");
        return;
      }

      try {
        const formData = new FormData();
        formData.append('baseTrackingID', this.bulkCertData.baseTrackingID);
        formData.append('certGivenDate', this.bulkCertData.certGivenDate);

        this.bulkCertData.certificates.forEach((file, index) => {
          if (!file) {
            throw new Error(`Please upload a certificate file for all registrants. Missing file at index ${index}`);
          }
          formData.append(`certificates[${index}]`, file);
        });

        const response = await axios.post(
          import.meta.env.VITE_API_BASE_URL + `trainings/${this.selectedTraining.trainingID}/certificates/bulk`,
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              'Content-Type': 'multipart/form-data',
            },
          }
        );

        alert("Certificates issued successfully to all registrants!");
        this.closeBulkCertModal();
        await this.openRegistrantsModal(this.selectedTraining); // Refresh list
      } catch (error) {
        console.error("Error issuing bulk certificates:", error);
        if (error.response) {
          alert(error.response.data.message || "Failed to issue certificates.");
        } else {
          alert(error.message || "An error occurred while issuing certificates.");
        }
      }
    },


    async fetchTags() {
      try {
        const response = await axios.get(import.meta.env.VITE_API_BASE_URL + '/tags');
        this.tagOptions = response.data; // Update tagOptions correctly
      } catch (error) {
        console.error('Error fetching tags:', error);
      }
    },

    toggleTag(tagID) {
      const normalizedId = Number(tagID);
      const index = this.newTraining.Tags.indexOf(normalizedId);
      if (index > -1) {
        this.newTraining.Tags.splice(index, 1); // Remove tag if already selected
      } else {
        this.newTraining.Tags.push(normalizedId); // Add tag if not selected
      }
    },

    isTagSelected(tagID) {
      const normalizedId = Number(tagID);
      return this.newTraining.Tags.includes(normalizedId);
    },

    removeTag(tagID) {
      const normalizedId = Number(tagID);
      const index = this.newTraining.Tags.indexOf(normalizedId);
      if (index > -1) {
        this.newTraining.Tags.splice(index, 1); // Remove the tag
      }
    },

    async addTag() {
      const newTag = this.newTagName.trim();
      if (newTag) {
        try {
          // Send the new tag to the backend
          const response = await axios.post(import.meta.env.VITE_API_BASE_URL + '/tags', {
            TagName: newTag
          });

          // Add the new tag to the tagOptions and newTraining.Tags
          this.tagOptions.push(response.data); // Assuming response.data returns the new tag object
          this.newTraining.Tags.push(Number(response.data.TagID)); // Add the new tag ID to the selected tags

          // Clear the input field
          this.newTagName = '';
          alert("Tag added successfully!");
        } catch (error) {
          console.error("Error adding tag:", error);
          alert("Failed to add tag.");
        }
      } else {
        alert("Please enter a tag name.");
      }
    },
    async sendCertificateDetails() {
    if (!this.selectedRegistrant || !this.selectedRegistrant.id) {
      console.error("No registrant ID found!");
      return;
    }

    const formData = new FormData();
    formData.append("certificateTrackingID", this.selectedRegistrant.certificateTrackingID);
    formData.append("certificateGivenDate", this.selectedRegistrant.certificateGivenDate);
    if (this.selectedRegistrant.uploadedFile) {
      formData.append("file", this.selectedRegistrant.uploadedFile);
    }

    try {
      const res = await axios.put(
        `https://pathfinder-development-production.up.railway.app/api/registrations/${this.selectedRegistrant.id}/certificate`,
        formData,
        { headers: { "Content-Type": "multipart/form-data" } }
      );
      console.log("Certificate uploaded successfully", res.data);
      this.showCertUploadModal = false; // optionally close modal
    } catch (err) {
      console.error("Error uploading certificate: ", err);
    }
  },

    getTagName(tagID) {
      const normalizedId = Number(tagID);
      const tag = this.tagOptions.find(t => Number(t.TagID) === normalizedId);
      return tag ? tag.TagName : normalizedId; // Return the tag name or ID if not found
    },

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
          import.meta.env.VITE_API_BASE_URL + `/trainings/${training.trainingID}/registrants`,
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
  console.log("REGISTRANT DATA:", registrant);

  this.selectedRegistrant = {
    ...registrant,
    certificateTrackingID: registrant.id || "",
    certificateGivenDate: registrant.certificateGivenDate || "",
    uploadedFile: null,
  };

  console.log("SELECTED REGISTRANT:", this.selectedRegistrant);
  this.showCertUploadModal = true;
},

    closeCertUploadModal() {
      this.showCertUploadModal = false;
      this.selectedRegistrant = { name: "", dateRegistered: "", img: "" };
    },

    handleFileUpload(event) {
      this.selectedRegistrant.uploadedFile = event.target.files[0];
    },

  sendCertificateDetails() {
    if (!this.selectedRegistrant.uploadedFile) {
      alert("Please select a certificate file before sending.");
      return;
    }
    
    this.sendCertificate(
      this.selectedRegistrant.uploadedFile,
      this.selectedRegistrant.id
    ).then(() => {
      this.closeCertUploadModal();
    }).catch(err => {
      console.error(err);
      alert("Failed to send certificate.");
    });
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


    async fetchTrainings() {
      try {
        const storedUser = localStorage.getItem("user");
        const parsedUser = storedUser ? JSON.parse(storedUser) : null;
        const organizationID = parsedUser?.organizationID ?? parsedUser?.organization?.organizationID ?? null;

        let newTrainings = [];
        try {
          const { data } = await api.get("/organization/trainings");
          newTrainings = data;
        } catch (err) {
          console.warn("Org trainings endpoint unavailable, falling back:", err?.response?.status);
          const storedUser = localStorage.getItem("user");
          const parsedUser = storedUser ? JSON.parse(storedUser) : null;
          const organizationID = parsedUser?.organizationID ?? parsedUser?.organization?.organizationID ?? null;

          const { data } = await api.get("/trainings", {
            params: organizationID ? { organizationID } : {},
          });
          newTrainings = data;
        }

        newTrainings.forEach(training => {
          const existingIndex = this.upcomingtrainings.findIndex(t => t.trainingID === training.trainingID);

          if (existingIndex > -1) {
            this.upcomingtrainings[existingIndex] = { ...this.upcomingtrainings[existingIndex], ...training };
          } else {
            this.upcomingtrainings.push(training);
          }

          // ✅ Schedule QR using composable
          scheduleQR(training);
        });
      } catch (error) {
        console.error("Error fetching trainings:", error);
      }
    },

    openTrainingPopup() {
      this.showTrainingPopup = true;
      this.fetchTags(); // Load tags into dropdown
    },

    async openTrainingPopup(training = null) {
      await this.fetchTags(); // Load tags into dropdown

      if (training) {
        this.isEditMode = true;
        this.trainingToEditId = training.trainingID;
        this.newTraining = {
          ...training,
          Tags: training.Tags
            ? training.Tags.map(tag => Number(tag.TagID ?? tag.tagID ?? tag.id))
            : []
        };
      } else {
        this.isEditMode = false;
        this.newTraining = {
          title: "",
          description: "",
          date: "",
          startTime: "",
          endTime: "",
          mode: "",
          location: "",
          trainingLink: "",
          Tags: []
        };
      }

      if (!Array.isArray(this.newTraining.Tags)) {
        this.newTraining.Tags = [];
      }

      this.showTrainingPopup = true;
    },

    closeTrainingPopup() {
      this.showTrainingPopup = false;
      this.newTraining = {
        title: "",
        description: "",
        date: "",
        startTime: "",
        endTime: "",
        mode: "",
        location: "",
        trainingLink: "",
        Tags: []
      };
      this.newTagName = ""; // optional: clear the tag input too
    },

    async updateTraining(trainingID) {
      const training = this.upcomingtrainings.find(t => t.trainingID === trainingID);
      if (!training) {
        alert("Training not found.");
        return;
      }

      const scheduleParts = training.schedule?.includes("T")
        ? training.schedule.split("T")
        : training.schedule?.split(" ") || [];

      this.newTraining.title = training.title;
      this.newTraining.description = training.description;
      this.newTraining.date = scheduleParts[0] || "";
      this.newTraining.startTime = scheduleParts[1]?.slice(0, 5) || "";
      this.newTraining.endTime = training.end_time
        ? (training.end_time.includes("T") ? training.end_time.split("T")[1] : training.end_time.split(" ")[1])?.slice(0, 5)
        : "";
      this.newTraining.mode = training.mode;
      this.newTraining.location = training.location || "";
      this.newTraining.trainingLink = training.training_link || "";
      await this.fetchTags();

      // ✅ Fix for tags
      this.newTraining.Tags = training.Tags
        ? training.Tags.map(tag => Number(tag.TagID ?? tag.tagID ?? tag.id))
        : [];

      if (!Array.isArray(this.newTraining.Tags)) {
        this.newTraining.Tags = [];
      }

      this.isEditMode = true;
      this.trainingToEditId = trainingID;
      this.showTrainingPopup = true;
      this.closeAllMenus();

      console.log("Prefilled training:", this.newTraining);
    },

    // For saving (create/update) training
    async saveTraining() {
      try {
        if (!this.newTraining.date || !this.newTraining.startTime) {
          alert("PLEASE SELECT BOTH A DATE AND TIME FOR THE TRAINING");
          return;
        }

        const combinedSchedule = `${this.newTraining.date} ${this.newTraining.startTime}`; // NO :00
        const endTimeSchedule = this.newTraining.endTime ? `${this.newTraining.date} ${this.newTraining.endTime}` : null;

        const payload = {
          title: this.newTraining.title,
          description: this.newTraining.description,
          schedule: combinedSchedule,
          end_time: endTimeSchedule,
          mode: this.newTraining.mode,
          location: this.newTraining.mode === "On-Site" ? this.newTraining.location || null : null,
          training_link: this.newTraining.mode === "Online" ? this.newTraining.trainingLink || null : null,
          Tags: this.newTraining.Tags || []
        };

        const token = localStorage.getItem("token");

        if (this.isEditMode && this.trainingToEditId) {
          await axios.put(
            `${import.meta.env.VITE_API_BASE_URL}/trainings/${this.trainingToEditId}`,
            payload,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          alert("✅ TRAINING UPDATED SUCCESSFULLY!");
        } else {
          await axios.post(
            `${import.meta.env.VITE_API_BASE_URL}/trainings`,
            payload,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          alert("✅ TRAINING POSTED SUCCESSFULLY!");
        }

        await this.fetchTrainings();
        this.closeTrainingPopup();
        this.isEditMode = false;
        this.trainingToEditId = null;

      } catch (error) {
        console.error("ERROR SAVING TRAINING:", error.response?.data || error);
        alert("❌ SOMETHING WENT WRONG WHILE SAVING THE TRAINING");
      }
    },

    // For deleting training
    async deleteTraining(trainingID) {
      try {
        if (!confirm("Are you sure you want to delete this training?")) return;

        const token = localStorage.getItem("token");

        await axios.delete(
          `${import.meta.env.VITE_API_BASE_URL}/trainings/${trainingID}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        alert("✅ Training deleted successfully!");
        await this.fetchTrainings();

        if (this.activeTrainingId === trainingID) {
          this.qrCodeValue = null;
          this.qrExpiresAt = null;
          this.activeTrainingId = null;
          if (this.qrExpireTimeout) clearTimeout(this.qrExpireTimeout);
        }

      } catch (error) {
        console.error("Failed to delete training:", error.response?.data || error);
        alert("❌ Something went wrong while deleting the training.");
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

  },


  mounted() {
    this.fetchTrainings();
    document.addEventListener("click", this.handleOutsideClick);

    // Poll every 30 seconds to update trainings
    this.trainingPollInterval = setInterval(() => {
      this.fetchTrainings();
    }, 30000);
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
        .sort((a, b) => new Date(b.schedule) - new Date(a.schedule));
    },
    filteredUpcoming() {
      const query = this.globalSearchQuery.toLowerCase();
      if (!query) return this.sortedUpcomingTrainings;
      return this.sortedUpcomingTrainings.filter(training =>
        training.title.toLowerCase().startsWith(query) // 🔹 only matches if letters typed are in order from the start
      );
    },
    filteredCompleted() {
      const query = this.globalSearchQuery.toLowerCase();
      if (!query) return this.sortedCompletedTrainings;
      return this.sortedCompletedTrainings.filter(training =>
        training.title.toLowerCase().startsWith(query)
      );
    },
    visibleFilteredUpcoming() {
      return this.showAllUpcoming
        ? this.filteredUpcoming
        : this.filteredUpcoming.slice(0, 4);
    },
    visibleFilteredCompleted() {
      return this.showAllCompleted
        ? this.filteredCompleted
        : this.filteredCompleted.slice(0, 4);
    },

    visibleUpcomingTrainings() {
      const orgId = this.currentOrganizationId;
      const list = this.sortedUpcomingTrainings.filter(
        training => training.organization_id === orgId
      );
      return this.showAllUpcoming ? list : list.slice(0, 4);
    },

    visibleCompletedTrainings() {
      const orgId = this.currentOrganizationId;
      const list = this.sortedCompletedTrainings.filter(
        training => training.organization_id === orgId
      );
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
        .sort((a, b) => new Date(b.schedule) - new Date(a.schedule));
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
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
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
  gap: 1.5rem;
  margin-top: 1rem;
  overflow: visible;
  transition: max-height 0.4s ease;
}

/* Collapsed view */
.trainings-grid.collapsed {
  max-height: 600px;
  /* adjust depending on your card height */
}

/* Expanded view */
.trainings-grid.expanded {
  max-height: 2000px;
  overflow: visible;
}

/* Default - Large screens (4 per row) */
.trainings-grid {
  grid-template-columns: repeat(4, 1fr);
}

/* Medium screens (3 per row) */
@media (max-width: 1200px) {
  .trainings-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Small screens (2 per row) */
@media (max-width: 900px) {
  .trainings-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Extra small screens (1 per row) */
@media (max-width: 600px) {
  .trainings-grid {
    grid-template-columns: repeat(1, 1fr);
  }
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

.bulk-issue-btn
{
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
.registrant-certid {
  font-size: 1.1rem;
  font-weight: 600;
  color: #4a4a4a;
  text-align: left;
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

/* Search Bar CSS*/
.global-search-bar {
  width: 600px;
  padding: 10px 14px;
  border: 1px solid #aaaaaa;
  border-radius: 8px;
  background-color: #fff;
  outline: none;
  transition: all 0.2s ease;
  color: #000;
}

.global-search-bar::placeholder {
  color: #9ca3af;
  /* same as Tailwind’s text-gray-400 */
  font-style: italic;
  /* optional — match whatever Training uses */
  opacity: 1;
  /* ensures consistent rendering */
}

.global-search-bar:focus {
  border-color: #44576d;
  box-shadow: 0 0 5px rgba(68, 87, 109, 0.2);
}
</style>
