  <template>
    <div class="organization-calendar">

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
            <img v-if="logoUrl" :src="logoUrl" alt="Organization Logo" class="avatar-img" />
            <div v-else class="avatar-placeholder">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20.59 22C20.59 18.13 16.74 15 12 15C7.26 15 3.41 18.13 3.41 22" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          </div>

          <!-- Profile Section (only when sidebar is open) -->
          <transition name="fade">
            <div v-if="isSidebarOpen" class="profile-section">
              <h3 class="org-name">
                {{ organizationName }}
                <span v-if="organizationStatus !== null" class="org-status-inline" :class="organizationStatusClass">
                  <svg v-if="isOrganizationVerified" class="org-status-icon-inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <svg v-else class="org-status-icon-inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span>{{ organizationStatusLabel }}</span>
                </span>
              </h3>
              <div class="profile-actions">
                <div class="action" @click="navigateTo({ name: 'OrgUpdateProfile' })">
                  <!-- Update Profile Icon -->
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M11.7278 8.27191C12.7534 9.87525 14.1247 11.2375 15.7464 12.2534L8.85673 19.144C8.43166 19.569 8.21841 19.7816 7.95731 19.9213C7.69637 20.0609 7.40171 20.1199 6.81278 20.2377L3.73563 20.853C3.40302 20.9195 3.23649 20.9525 3.14188 20.8578C3.04759 20.7632 3.08035 20.5971 3.14677 20.2651L3.76298 17.1879C3.88087 16.5985 3.93965 16.3035 4.07938 16.0424C4.21912 15.7814 4.43173 15.569 4.85673 15.144L11.7278 8.27191ZM16.1116 4.03656C16.6711 3.75929 17.3284 3.75931 17.888 4.03656C18.1821 4.18229 18.455 4.45518 19.0003 5.00043C19.5453 5.54545 19.8184 5.81774 19.9641 6.11175C20.2414 6.67123 20.2413 7.32861 19.9641 7.88812C19.8184 8.18221 19.5455 8.45517 19.0003 9.00043L17.2034 10.7963C15.5308 9.84498 14.1456 8.46859 13.1819 6.81781L15.0003 5.00043C15.5453 4.45539 15.8176 4.18234 16.1116 4.03656Z"
                      fill="#FFFDFD" />
                  </svg>
                  <span>Update Profile</span>
                </div>
                <div class="action" @click="navigateTo({ name: 'OrgChangePassword' })">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
                      stroke="#FFFDFD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path
                      d="M19.4 15C19.2669 15.3016 19.2272 15.6362 19.286 15.9606C19.3448 16.285 19.4995 16.5843 19.73 16.82L19.79 16.88C19.976 17.0657 20.1235 17.2863 20.2241 17.5291C20.3248 17.7719 20.3766 18.0322 20.3766 18.295C20.3766 18.5578 20.3248 18.8181 20.2241 19.0609C20.1235 19.3037 19.976 19.5243 19.79 19.71C19.6043 19.896 19.3837 20.0435 19.1409 20.1441C18.8981 20.2448 18.6378 20.2966 18.375 20.2966C18.1122 20.2966 17.8519 20.2448 17.6091 20.1441C17.3663 20.0435 17.1457 19.896 16.96 19.71L16.9 19.65C16.6643 19.4195 16.365 19.2648 16.0406 19.206C15.7162 19.1472 15.3816 19.1869 15.08 19.32C14.7842 19.4468 14.532 19.6572 14.3543 19.9255C14.1766 20.1938 14.0813 20.5082 14.08 20.83V21C14.08 21.5304 13.8693 22.0391 13.4942 22.4142C13.1191 22.7893 12.6104 23 12.08 23C11.5496 23 11.0409 22.7893 10.6658 22.4142C10.2907 22.0391 10.08 21.5304 10.08 21V20.91C10.0723 20.579 9.96512 20.258 9.77251 19.9887C9.5799 19.7194 9.31074 19.5143 9 19.4C8.69838 19.2669 8.36381 19.2272 8.03941 19.286C7.71502 19.3448 7.41568 19.4995 7.18 19.73L7.12 19.79C6.93425 19.976 6.71368 20.1235 6.47088 20.2241C6.22808 20.3248 5.96783 20.3766 5.705 20.3766C5.44217 20.3766 5.18192 20.3248 4.93912 20.2241C4.69632 20.1235 4.47575 19.976 4.29 19.79C4.10405 19.6043 3.95653 19.3837 3.85588 19.1409C3.75523 18.8981 3.70343 18.6378 3.70343 18.375C3.70343 18.1122 3.75523 17.8519 3.85588 17.6091C3.95653 17.3663 4.10405 17.1457 4.29 16.96L4.35 16.9C4.58054 16.6643 4.73519 16.365 4.794 16.0406C4.85282 15.7162 4.81312 15.3816 4.68 15.08C4.55324 14.7842 4.34276 14.532 4.07447 14.3543C3.80618 14.1766 3.49179 14.0813 3.17 14.08H3C2.46957 14.08 1.96086 13.8693 1.58579 13.4942C1.21071 13.1191 1 12.6104 1 12.08C1 11.5496 1.21071 11.0409 1.58579 10.6658C1.96086 10.2907 2.46957 10.08 3 10.08H3.09C3.42099 10.0723 3.742 9.96512 4.01129 9.77251C4.28059 9.5799 4.48571 9.31074 4.6 9C4.73312 8.69838 4.77282 8.36381 4.714 8.03941C4.65519 7.71502 4.50054 7.41568 4.27 7.18L4.21 7.12C4.02405 6.93425 3.87653 6.71368 3.77588 6.47088C3.67523 6.22808 3.62343 5.96783 3.62343 5.705C3.62343 5.44217 3.67523 5.18192 3.77588 4.93912C3.87653 4.69632 4.02405 4.47575 4.21 4.29C4.39575 4.10405 4.61632 3.95653 4.85912 3.85588C5.10192 3.75523 5.36217 3.70343 5.625 3.70343C5.88783 3.70343 6.14808 3.75523 6.39088 3.85588C6.63368 3.95653 6.85425 4.10405 7.04 4.29L7.1 4.35C7.33568 4.58054 7.63502 4.73519 7.95941 4.794C8.28381 4.85282 8.61838 4.81312 8.92 4.68H9C9.29577 4.55324 9.54802 4.34276 9.72569 4.07447C9.90337 3.80618 9.99872 3.49179 10 3.17V3C10 2.46957 10.2107 1.96086 10.5858 1.58579C10.9609 1.21071 11.4696 1 12 1C12.5304 1 13.0391 1.21071 13.4142 1.58579C13.7893 1.96086 14 2.46957 14 3V3.09C14.0013 3.41179 14.0966 3.72618 14.2743 3.99447C14.452 4.26276 14.7042 4.47324 15 4.6C15.3016 4.73312 15.6362 4.77282 15.9606 4.714C16.285 4.65519 16.5843 4.50054 16.82 4.27L16.88 4.21C17.0657 4.02405 17.2863 3.87653 17.5291 3.77588C17.7719 3.67523 18.0322 3.62343 18.295 3.62343C18.5578 3.62343 18.8181 3.67523 19.0609 3.77588C19.3037 3.87653 19.5243 4.02405 19.71 4.21C19.896 4.39575 20.0435 4.61632 20.1441 4.85912C20.2448 5.10192 20.2966 5.36217 20.2966 5.625C20.2966 5.88783 20.2448 6.14808 20.1441 6.39088C20.0435 6.63368 19.896 6.85425 19.71 7.04L19.65 7.1C19.4195 7.33568 19.2648 7.63502 19.206 7.95941C19.1472 8.28381 19.1869 8.61838 19.32 8.92V9C19.4468 9.29577 19.6572 9.54802 19.9255 9.72569C20.1938 9.90337 20.5082 9.99872 20.83 10H21C21.5304 10 22.0391 10.2107 22.4142 10.5858C22.7893 10.9609 23 11.4696 23 12C23 12.5304 22.7893 13.0391 22.4142 13.4142C22.0391 13.7893 21.5304 14 21 14H20.91C20.5882 14.0013 20.2738 14.0966 20.0055 14.2743C19.7372 14.452 19.5268 14.7042 19.4 15Z"
                      stroke="#FFFDFD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span>Change Password</span>
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
          <div class="icon signout" @click="$router.push('/loginform')">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
              stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Sign Out</span>
          </div>

        </aside>
      </transition na>

      <!-- Main content -->
      <main class="content">
        <header class="topbar">
          <div class="Pathfinder-wrapper">
            <span class="logo-text">Pathfinder</span>
          </div>
        </header>

        <section class="calendar-section">
          <div class="calendar-container">
            <!-- Calendar -->
            <div class="calendar-wrapper">
              <div class="calendar-header">
                <button @click="prevMonth">‹</button>
                <h2>{{ currentMonthName }} {{ currentYear }}</h2>
                <button @click="nextMonth">›</button>
              </div>

              <div class="calendar-grid">
                <div class="day-name" v-for="day in dayNames" :key="day">{{ day }}</div>

                <div v-for="(date, index) in calendarDays" :key="index" class="day-cell"
                  :class="{ today: isToday(date), event: hasEvent(date) }"
                  :style="{ backgroundColor: getEventColor(date) }" @click="openDayModal(date)">
                  <span v-if="date" class="date-number">{{ date.getDate() }}</span>

                  <div v-if="date && getEventsByDate(date).length" class="events-list">
                    <div v-if="date && getEventTitles(date).length" class="events-list">
                      <!-- Show only first 2 events -->
                      <div v-for="(event, i) in getEventTitles(date).slice(0, 1)" :key="i" class="event-title"
                        :class="event.type">
                        {{ event.title }}
                      </div>

                      <!-- Show "+ Show more" if there are more than 2 events -->
                      <div v-if="getEventTitles(date).length > 1" class="show-more"
                        @click.stop="openEventDetailsByDate(date)">
                        + Show more
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="calendar-legend">
                <div class="legend-item">
                  <span class="training">🟦</span> Trainings
                </div>
                <div class="legend-item">
                  <span class="career">🟨</span> Careers
                </div>
                <div class="legend-item">
                  <span class="interview">🟥</span> Scheduled Interviews
                </div>
              </div>
            </div>

            <!-- Right-side Panel -->
            <div class="calendar-side">
              <!-- When there are events -->
              <template
                v-if="selectedEvents && (selectedEvents.trainings?.length || selectedEvents.careers?.length || selectedEvents.interviews?.length)">
                <h3>Events on {{ selectedDate }}</h3>

                <!-- Trainings -->
                <div v-if="selectedEvents.trainings.length" class="event-group">
                  <h4 class="group-title">🟦 Trainings</h4>
                  <div v-for="(event, i) in selectedEvents.trainings" :key="'t-' + i" class="event-details-card">
                    <h5>{{ event.title }}</h5>
                    <p class="event-info" v-if="event.description"><strong>Description:</strong></p>
                    <p class="event-info" v-if="event.description">{{ event.description }}</p>
                    <p class="event-info" v-if="event.startTime || event.endTime">
                      <strong>Date and Time:</strong> 
                      <span v-if="event.startTime">{{ formatTime12Hour(event.startTime) }}</span>
                      <span v-if="event.startTime && event.endTime"> - </span>
                      <span v-if="event.endTime">{{ formatTime12Hour(event.endTime) }}</span>
                    </p>
                    <p class="event-info" v-if="event.mode"><strong>Mode:</strong> {{ event.mode }}</p>
                    <p class="event-info" v-if="event.location"><strong>Location:</strong> {{ event.location }}</p>
                    <p class="event-info" v-if="event.link">
                      <strong>Link:</strong> 
                      <a :href="event.link" target="_blank" rel="noopener noreferrer" class="event-link">{{ event.link }}</a>
                    </p>
                  </div>
                </div>

                <!-- Careers -->
                <div v-if="selectedEvents.careers.length" class="event-group">
                  <h4 class="group-title">🟨 Careers</h4>
                  <div v-for="(event, i) in selectedEvents.careers" :key="'c-' + i" class="event-details-card">
                    <h5>{{ event.title }}</h5>
                    <p class="event-info" v-if="event.qualificationStandard"><strong>Qualification Standard:</strong></p>
                    <p class="event-info" v-if="event.qualificationStandard">{{ event.qualificationStandard }}</p>
                    <p class="event-info" v-if="event.requirements"><strong>Requirements:</strong></p>
                    <p class="event-info" v-if="event.requirements">{{ event.requirements }}</p>
                    <p class="event-info" v-if="event.details"><strong>Details:</strong></p>
                    <p class="event-info" v-if="event.details">{{ event.details }}</p>
                    <p class="event-info" v-if="event.postingDate"><strong>Posting Date:</strong> {{ event.postingDate }}</p>
                    <p class="event-info" v-if="event.closingDate"><strong>Closing Date:</strong> {{ event.closingDate }}</p>
                  </div>
                </div>

                <!-- Scheduled Interviews -->
                <div v-if="selectedEvents.interviews.length" class="event-group">
                  <h4 class="group-title">🟥 Scheduled Interviews</h4>
                  <div v-for="(event, i) in selectedEvents.interviews" :key="'i-' + i" class="event-details-card">
                    <h5>{{ event.title }}</h5>
                    <p class="event-info"><strong>Applicant:</strong> {{ event.applicantName || event.applicantID || 'N/A' }}</p>
                    <p class="event-info" v-if="event.interviewTime">
                      <strong>Time:</strong> {{ formatTime12Hour(event.interviewTime) }}
                    </p>
                    <p class="event-info" v-else-if="event.interviewSchedule">
                      <strong>Time:</strong> {{ formatTime12Hour(event.interviewSchedule) || 'TBD' }}
                    </p>
                    <p class="event-info" v-else><strong>Time:</strong> TBD</p>
                    <p class="event-info" v-if="event.mode"><strong>Mode:</strong> {{ event.interviewMode }}</p>
                    <p class="event-info" v-if="event.location"><strong>Location:</strong> {{ event.interviewLocation }}
                    </p>
                    <p class="event-info" v-if="event.interviewLink"><strong>Link:</strong>
                      <a :href="event.interviewLink" target="_blank" rel="noopener noreferrer">{{
                        event.interviewLink }}</a>
                    </p>
                  </div>
                </div>

                <button class="close-btn" @click="closeSidebar">Close</button>
              </template>

              <!-- When no date is selected -->
              <template v-else>
                <h3>Select a date</h3>
                <p>Click a date in the calendar to view its trainings and careers.</p>
              </template>
            </div>
          </div>
        </section>
      </main>
    </div>
  </template>

<script>
import axios from "axios";
import api from "@/composables/api";
import { getImageUrl } from "@/lib/supabase.js";

export default {
  name: "OrganizationCalendar",
  data() {
    return {
      organizationLogo: null,
      currentDate: new Date(),
      selectedEvents: { trainings: [], careers: [], scheduledInterviews: [] },
      isSidebarOpen: true,
      dayNames: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],

      // 👇 added for modal functionality
      showModal: false,
      selectedDate: null,
      showMenu: false,

      trainings: [],
      careers: [],
      scheduledInterviews: [],
    }
  },
  setup() {
    const events = ref({}); // calendar events by date

    return { events };
  },
  computed: {
    logoUrl() {
      if (this.organizationLogo) {
        const logoPath = this.organizationLogo.logo_directory || 
                        this.organizationLogo.Logo_directory || 
                        this.organizationLogo.logoPath;
        if (logoPath) {
          const url = getImageUrl(logoPath, "Requirements");
          return url || null;
        }
      }
      return null;
    },
    monthYear() {
      return this.currentDate.toLocaleString("default", {
        month: "long",
        year: "numeric"
      });
    },
    calendarDays() {
      const year = this.currentDate.getFullYear();
      const month = this.currentDate.getMonth();

      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const daysInMonth = lastDay.getDate();

      const days = [];
      for (let i = 0; i < firstDay.getDay(); i++) days.push(null);
      for (let d = 1; d <= daysInMonth; d++) days.push(new Date(year, month, d));

      return days;
    },

    formattedSelectedDate() {
      if (!this.selectedDate) return "";
      return this.selectedDate.toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric"
      });
    }
  },
  methods: {

    formatTime12Hour(time) {
      if (!time) return "";

      // Handle both "HH:mm" format and datetime strings
      let hourStr, minuteStr;
      
      if (typeof time === 'string') {
        // Extract time from "HH:mm" or "YYYY-MM-DD HH:mm" or "YYYY-MM-DDTHH:mm:ss"
        const timeMatch = time.match(/(\d{1,2}):(\d{2})/);
        if (timeMatch) {
          hourStr = timeMatch[1];
          minuteStr = timeMatch[2];
        } else {
          return ""; // Invalid format
        }
      } else {
        return ""; // Not a string
      }

      // Parse hours and minutes
      const hours = parseInt(hourStr, 10);
      const minutes = parseInt(minuteStr, 10);

      // Validate parsed values
      if (isNaN(hours) || isNaN(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
        return "";
      }

      // Determine AM/PM
      const ampm = hours >= 12 ? "PM" : "AM";

      // Convert to 12-hour format
      const displayHour = hours % 12 === 0 ? 12 : hours % 12;

      // Return formatted time (exact time from database, just converted to 12-hour format)
      return `${displayHour}:${minutes.toString().padStart(2, "0")} ${ampm}`;
    },

    async fetchApplications() {
      try {
        const user = JSON.parse(localStorage.getItem("user"));
        const token = localStorage.getItem("token");
        if (!user || !token) return;

        // Call the API that returns applications with interview schedule
        const { data: apps } = await axios.get(
          import.meta.env.VITE_API_BASE_URL + "/applications",
          { headers: { Authorization: `Bearer ${token}` } }
        );

        // Filter only applications that have an interview schedule
        const careerEvents = apps
          .filter((app) => app.interviewSchedule) // skip applications without interviews
          .map((app) => ({
            id: app.applicationID,
            careerID: app.careerID,
            title: app.career?.position || "Career Interview",
            date: new Date(app.interviewSchedule).toISOString().split("T")[0],
            type: "career",
            interviewSchedule: app.interviewSchedule,
            interviewMode: app.interviewMode,
            interviewLink: app.interviewLink,
            interviewLocation: app.interviewLocation,
            organization: app.career?.organization,
          }));

        // Merge career events into your existing events map
        careerEvents.forEach((event) => {
          if (!events.value[event.date]) events.value[event.date] = [];
          events.value[event.date].push(event);
        });

        console.log("📅 Career events merged:", careerEvents);
      } catch (err) {
        console.error("Failed to fetch applications:", err);
      }
    },

    async fetchInterviews() {
      const token = localStorage.getItem("token");
      if (!token) return;

      try {
        const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/applications/interviews", {
          headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
        });
        console.log("API /applications/interviews response:", res.data);

        const interviews = res.data.map((interview) => {
          const schedule = interview.interviewSchedule || interview.schedule || null;
          
          // Parse date string directly to avoid timezone issues
          let date = "";
          let interviewTime = "";
          
          if (schedule) {
            // Handle format "2025-12-03 14:30" or "2025-12-03T14:30:00" or ISO string
            const dateTimeMatch = String(schedule).match(/(\d{4}-\d{2}-\d{2})[T\s](\d{2}):(\d{2})/);
            if (dateTimeMatch) {
              date = dateTimeMatch[1]; // "2025-12-03"
              interviewTime = `${dateTimeMatch[2]}:${dateTimeMatch[3]}`; // "14:30"
            } else {
              // Fallback to Date object if format doesn't match
              const scheduleDate = new Date(schedule);
              if (!isNaN(scheduleDate.getTime())) {
                date = scheduleDate.toISOString().split("T")[0];
                interviewTime = scheduleDate.toTimeString().slice(0, 5);
              }
            }
          }

          return {
            id: interview.id || interview.applicationID || interview.interviewID,
            applicantID: interview.applicantID || interview.applicant_id || null,
            applicantName: interview.applicantName || null,
            title: interview.title || interview.interwiewStatus || "For Interview",
            date: date,
            interviewSchedule: schedule,
            interviewTime: interviewTime, // Store exact time for display
            interviewMode: interview.interviewMode || interview.mode || null,
            interviewLink: interview.interviewLink || interview.link || null,
            interviewLocation: interview.interviewLocation || interview.location || null,
            organization: interview.organizationName || interview.organization || null,
          };
        });

        this.scheduledInterviews = interviews;
        console.log("📅 Scheduled interviews loaded:", interviews.length);
      } catch (err) {
        console.error("Error fetching interviews:", err);
      }
    },

    async fetchTrainings() {
      let res;
      try {
        res = await api.get("/organization/trainings");
      } catch (err) {
        console.warn("Org trainings endpoint unavailable for calendar, falling back:", err?.response?.status);
        const token = localStorage.getItem("token");
        if (!token) return;

        const storedUser = localStorage.getItem("user");
        const parsedUser = storedUser ? JSON.parse(storedUser) : null;
        const organizationID = parsedUser?.organizationID ?? parsedUser?.organization?.organizationID ?? null;

        res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/trainings", {
          headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
          params: organizationID ? { organizationID } : {},
        });
      }

      // Handle multiple schedules per training
      const trainingEvents = [];
      res.data.forEach(t => {
        // If training has schedules array, create one event per schedule
        if (t.schedules && Array.isArray(t.schedules) && t.schedules.length > 0) {
          t.schedules.forEach((schedule, index) => {
            // Parse date string directly to avoid timezone issues
            // Format from backend: "2025-12-03 14:30" (Y-m-d H:i)
            const scheduleStr = schedule.schedule || "";
            const endTimeStr = schedule.end_time || "";
            
            // Extract date and time components directly from string
            let date = "";
            let startTime = "";
            let endTime = "";
            
            if (scheduleStr) {
              // Handle format "2025-12-03 14:30" or "2025-12-03T14:30:00"
              const dateTimeMatch = scheduleStr.match(/(\d{4}-\d{2}-\d{2})[T\s](\d{2}):(\d{2})/);
              if (dateTimeMatch) {
                date = dateTimeMatch[1]; // "2025-12-03"
                startTime = `${dateTimeMatch[2]}:${dateTimeMatch[3]}`; // "14:30"
              }
            }
            
            if (endTimeStr) {
              // Handle format "2025-12-03 14:30" or "2025-12-03T14:30:00"
              const endTimeMatch = endTimeStr.match(/(\d{4}-\d{2}-\d{2})[T\s](\d{2}):(\d{2})/);
              if (endTimeMatch) {
                endTime = `${endTimeMatch[2]}:${endTimeMatch[3]}`; // "16:00"
              }
            }

            trainingEvents.push({
              id: `${t.trainingID}-${schedule.trainingScheduleID || index}`,
              trainingID: t.trainingID,
              title: t.title,
              description: t.description,
              date: date, // "2025-12-03"
              startTime: startTime, // "14:30" (exact from database)
              endTime: endTime, // "16:00" (exact from database)
              mode: schedule.mode || null,
              location: schedule.location || null,
              link: schedule.trainingLink || schedule.training_link || null,
              organizationID: t.organization?.organizationID || t.organizationID,
            });
          });
        } else if (t.schedule) {
          // Backward compatibility: single schedule
          const scheduleStr = t.schedule || "";
          const endTimeStr = t.end_time || "";
          
          // Extract date and time components directly from string
          let date = "";
          let startTime = "";
          let endTime = "";
          
          if (scheduleStr) {
            const dateTimeMatch = scheduleStr.match(/(\d{4}-\d{2}-\d{2})[T\s](\d{2}):(\d{2})/);
            if (dateTimeMatch) {
              date = dateTimeMatch[1];
              startTime = `${dateTimeMatch[2]}:${dateTimeMatch[3]}`;
            }
          }
          
          if (endTimeStr) {
            const endTimeMatch = endTimeStr.match(/(\d{4}-\d{2}-\d{2})[T\s](\d{2}):(\d{2})/);
            if (endTimeMatch) {
              endTime = `${endTimeMatch[2]}:${endTimeMatch[3]}`;
            }
          }

          trainingEvents.push({
            id: t.trainingID,
            trainingID: t.trainingID,
            title: t.title,
            description: t.description,
            date: date,
            startTime: startTime,
            endTime: endTime,
            mode: t.mode || null,
            location: t.location || null,
            link: t.trainingLink || null,
            organizationID: t.organization?.organizationID || t.organizationID,
          });
        }
      });

      this.trainings = trainingEvents;
    },

    async fetchCareers() {
      const res = await api.get("/organization/careers");

      this.careers = res.data.map(c => ({
        id: c.careerID,
        title: c.position,
        details: c.detailsAndInstructions || c.details,
        qualificationStandard: c.qualificationStandard,
        requirements: c.requirements,
        letterAddress: c.applicationLetterAddress,
        postingDate: c.postingDate ? c.postingDate.split("T")[0] : null,
        closingDate: c.closingDate ? c.closingDate.split("T")[0] : null,
        // 👇 Fix the date formatting here
        date: c.deadlineOfSubmission ? c.deadlineOfSubmission.split("T")[0] : null,
        // 👇 Also fix organization mapping if your backend nests it
        organizationID: c.organization?.organizationID || c.organizationID,
      }));
    },
    openEventDetailsByDate(date) {
      this.selectedDate = date;
      this.openDayModal(date);
    },
    formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    prevMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() - 1);
      this.currentDate = new Date(this.currentDate);
    },
    nextMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() + 1);
      this.currentDate = new Date(this.currentDate);
    },
    isToday(date) {
      if (!date) return false;
      const today = new Date();
      return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
      );
    },
    hasEvent(date) {
      if (!date) return false;
      const formatted = this.formatDate(date); // ✅ define formatted here
      return (
        this.trainings.some(t => t.date === formatted) ||
        this.careers.some(c => c.date === formatted) ||
        this.scheduledInterviews.some(i => i.date === formatted)
      );
    },

    getEventTitles(date) {
      if (!date) return [];

      const formatted = this.formatDate(date);

      const trainings = this.trainings
        .filter(t => t.date === formatted)
        .map(t => ({ type: "training", title: "🟦 " + t.title }));

      const careers = this.careers
        .filter(c => c.date === formatted)
        .map(c => ({ type: "career", title: "🟨 " + c.title }));

      const interviews = this.scheduledInterviews
        .filter(i => i.date === formatted)
        .map(i => ({ type: "interview", title: "🟥 " + i.title }));

      return [...trainings, ...careers, ...interviews];
    },
    getEventColor(date) {
      if (!date) return "";
      const formatted = this.formatDate(date);
      if (this.trainings.some(t => t.date === formatted)) return "#F5F5F5";
      if (this.careers.some(c => c.date === formatted)) return "#F5F5F5";
      if (this.scheduledInterviews.some(i => i.date === formatted)) return "#FFE5E5";
      return "";
    },
    formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    getEventsByDate(date) {
      if (!date) return [];
      const formatted = this.formatDate(date);

      const trainings = this.trainings?.filter(t => t.date === formatted) || [];
      const careers = this.careers?.filter(c => c.date === formatted) || [];
      const interviews = this.scheduledInterviews?.filter(i => i.date === formatted) || [];

      return [...trainings, ...careers, ...interviews];
    },
    openEventDetails(event) {
      this.selectedEvent = event;
    },

    openDayModal(date) {
      if (!date) return;

      const formatted = this.formatDate(date);

      // Separate events by type
      const trainings = this.trainings
        .filter(t => t.date === formatted)
        .map(t => ({ ...t, type: "Training" }));

      const careers = this.careers
        .filter(c => c.date === formatted)
        .map(c => ({ ...c, type: "Career" }));

      const interviews = this.scheduledInterviews
        .filter(i => i.date === formatted)
        .map(i => ({ ...i, type: "Interview" }));

      this.selectedDate = formatted;
      this.selectedEvents = { trainings, careers, interviews };
    },

    closeSidebar() {
      this.selectedEvents = { trainings: [], careers: [] };
      this.selectedDate = null;
    },

    toggleMenu() {
      this.showMenu = !this.showMenu;
    },
    closeMenu() {
      this.showMenu = false;
    },

    editEvent() {
      alert("Edit function triggered!");
      this.closeMenu();
    },
    deleteEvent() {
      const confirmDelete = confirm("Are you sure you want to delete this event?");
      if (confirmDelete) {
        alert("Delete function triggered!");
        // Here you can add your logic to remove the event from `trainings` or `careers`
      }
      this.closeMenu();
    },

    closeModal() {
      this.showModal = false;
      this.selectedDate = null;
      this.selectedEvents = [];
      this.showMenu = false; // 👈 close menu when modal closes
    }
  },
  mounted() {
    // Get organization logo from localStorage
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      try {
        const user = JSON.parse(storedUser);
        if (user.organization) {
          this.organizationLogo = user.organization;
        } else if (user.logo_directory || user.Logo_directory || user.logoPath) {
          this.organizationLogo = user;
        }
      } catch (error) {
        console.error("Error parsing user from localStorage:", error);
      }
    }
    
    this.fetchTrainings();
    this.fetchCareers();
    this.fetchApplications();
    this.fetchInterviews();
  },
};
</script>


<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from 'vue-router';

const router = useRouter();
const isSidebarOpen = ref(true);
const organizationName = ref("");
const organizationStatus = ref(null);
const isOrganizationVerified = ref(false);

// Get org name from localStorage on mount
onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    const user = JSON.parse(storedUser);
    if (user.role === "organization") {
      organizationName.value = user.displayName || user.name;
      organizationStatus.value = user.status || user.Status || null;
      isOrganizationVerified.value = ["approved", "verified"].includes(
        (organizationStatus.value || "").toString().toLowerCase()
      );
    }
  }
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

onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    try {
      const user = JSON.parse(storedUser);
      if (user.role === "organization") {
        organizationName.value = user.displayName || user.name || "Organization";
        organizationStatus.value = user.status || user.Status || null;
        isOrganizationVerified.value = ["approved", "verified"].includes(
          (organizationStatus.value || "").toString().toLowerCase()
        );
      }
    } catch (e) {
      console.error("Failed to parse user from localStorage:", e);
    }
  }
});

// Computed properties for verification status
const organizationStatusLabel = computed(() => {
  return isOrganizationVerified.value ? "Verified" : "Unverified";
});

const organizationStatusClass = computed(() => {
  return isOrganizationVerified.value ? "org-status-verified" : "org-status-unverified";
});

// Toggle sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

// Calendar logic
const today = new Date();
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());

const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

const currentMonthName = computed(() =>
  new Date(currentYear.value, currentMonth.value).toLocaleString("default", {
    month: "long",
  })
);

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1);
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);

  const days = [];

  // Fill empty slots before first day
  for (let i = 0; i < firstDay.getDay(); i++) {
    days.push(null);
  }

  // Add all days of the month
  for (let d = 1; d <= lastDay.getDate(); d++) {
    days.push(new Date(currentYear.value, currentMonth.value, d));
  }

  return days;
});

const prevMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
};

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
};

const isToday = (date) => {
  if (!date) return false;
  return (
    date.getDate() === today.getDate() &&
    date.getMonth() === today.getMonth() &&
    date.getFullYear() === today.getFullYear()
  );
};
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

.organization-calendar {
  display: flex;
  height: 100vh;
  font-family: 'Poppins', sans-serif;
  background-color: #f4f4f4;
}

.calendar-container {
  width: 100%;
  max-width: 1150px;
}

/* Sidebar */
.sidebar {
  width: 230px;
  /* Expanded width - increased to fit Change Password option */
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

.sidebar .signout {
  margin-top: auto;
  /* ensures it's at the bottom */
}

.profile-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  /* centers everything under avatar */
  text-align: center;
  gap: 0.5rem;
  /* spacing between avatar, name, and actions */
  width: 100%;
}

.profile-section .avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background-color: #ccc;
  /* placeholder for logo/avatar */
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(255, 255, 255, 0.2);
}

.avatar {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background-color: #ccc;
  margin: 20px auto 10px auto;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.org-name {
  font-weight: 600;
  font-size: 14px;
  text-align: center;
  width: 100%;
  margin: 0 auto;
  line-height: 1.4;
}

.org-status-inline {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 500;
  padding: 2px 8px;
  border-radius: 999px;
  margin-left: 6px;
  vertical-align: middle;
}

.org-status-icon-inline {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
}

.org-status-verified {
  background-color: #10b981;
  color: #ffffff;
  border: 1px solid #059669;
}

.org-status-unverified {
  background-color: #f59e0b;
  color: #ffffff;
  border: 1px solid #d97706;
}

.profile-actions {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
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
  width: 100%;
  text-align: center;
}

.profile-actions .action svg {
  width: 14px;
  /* shrink the icon */
  height: 14px;
}

.profile-actions .action:hover {
  opacity: 0.8;
}

/* Calendar */
.calendar-section {
  display: flex;
  justify-content: center;
  width: 100%;
  padding: 1rem;
  box-sizing: border-box;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.calendar-header h2 {
  font-size: 20px;
  font-weight: 600;
  color: #44576D;
}

.calendar-header button {
  background: #44576D;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}

.calendar-header button:hover {
  background: #374151;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
}

.day-name {
  text-align: center;
  font-weight: bold;
  color: #555;
}

.day-cell {
  position: relative;
  border: 1px solid #ddd;
  border-radius: 6px;
  height: 100px;
  /* fixed height */
  width: 100%;
  /* fill column space */
  background-color: white;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  padding: 4px;
  box-sizing: border-box;
  overflow: hidden;
}

.day-cell.today {
  border: 2px solid #007bff;
}

.date-number {
  font-weight: bold;
  margin-bottom: 3px;
}

.events-list {
  flex: 1;
  width: 100%;
  overflow-y: auto;
  /* scrolls if too long, doesn’t resize cell */
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.event-title {
  font-size: 0.7rem;
  /* smaller text fits better */
  line-height: 1rem;
  word-wrap: break-word;
  white-space: normal;
  /* allows text to wrap */
  overflow: hidden;
  text-overflow: ellipsis;
}

.interview {
  color: #ef4444;
  /* emoji color fallback */
}

.event-title.interview {
  color: #ef4444;
  font-weight: 600;
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
  transform: translateX(170px);
  /* Adjust this to your sidebar width (230px - 60px = 170px) */
}

/* Calendar with sidebar */

.calendar-container {
  display: flex;
  align-items: flex-start;
  flex-direction: row;
  gap: 1.5rem;
  width: 100%;
  max-width: 1300px;
  /* slightly wider overall */
}

/* Calendar stays same */
.calendar-wrapper {
  flex: 3;
  /* increased from 2 → 3 */
  background: #fff;
  border-radius: 12px;
  padding: 1.2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Right-side panel */
.calendar-side {
  background: #fff;
  border-radius: 10px;
  padding: 20px;
  flex: 1;
  min-width: 250px;
}

.calendar-side h3 {
  margin-bottom: 15px;
  font-size: 18px;
  font-weight: 600;
  color: #333;
  border-bottom: 1px solid #eee;
  padding-bottom: 8px;
}

.event-details-card {
  color: black;
  background: #f8fafc;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 10px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.event-details-card h4 {
  margin-bottom: 4px;
  color: #1e293b;
}

.event-details-card h5 {
  color: black;
  font-weight: bold;
  font-style: italic;
}

.event-list {
  flex: 1;
  overflow-y: auto;
}

.event-item {
  margin-bottom: 12px;
  padding: 10px;
  border-radius: 8px;
  background: #f9f9f9;
}

.event-item.training {
  border-left: 4px solid #007bff;
}

.event-item.career {
  border-left: 4px solid #f1c40f;
}

.event-item strong {
  display: block;
  font-size: 15px;
}

.event-item p {
  font-size: 13px;
  color: #555;
  margin-top: 3px;
}

.no-events {
  color: #777;
  font-size: 14px;
  text-align: center;
  margin-top: 30px;
}

.close-btn {
  background: #44576D;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 10px;
}

.close-btn:hover {
  background: #374151;
}

.calendar-legend {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 15px;
  font-size: 0.9rem;
  color: #444;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.legend-color {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  display: inline-block;
}

.event-info {
  margin: 0.4rem 0;
  color: #333;
}

.event-link {
  color: #2563eb;
  text-decoration: none;
  word-break: break-all;
}

.event-link:hover {
  text-decoration: underline;
}

.show-more {
  font-size: 11px;
  color: #555;
  margin-top: 3px;
  cursor: pointer;
  text-align: left;
}

.show-more:hover {
  text-decoration: underline;
}

.event-title.training {
  color: #2563eb;
  /* blue */
  font-size: 12px;
}

.event-title.career {
  color: #fbbf24;
  /* yellow */
  font-size: 12px;
}

.calendar-side {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  flex: 1;
  min-width: 280px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.event-group {
  margin-bottom: 16px;
}

.group-title {
  color: #333;
  font-size: 1.1rem;
  margin-bottom: 8px;
  font-weight: 600;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 4px;
}

.event-details-card {
  background: #f8fafc;
  border-radius: 8px;
  padding: 10px;
  margin-bottom: 10px;
  transition: transform 0.1s ease;
}

.event-details-card:hover {
  transform: translateY(-2px);
}

.close-btn {
  background: #44576D;
  border: none;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 10px;
}

.close-btn:hover {
  background: #374151;
}

/* Color indicators */
.legend-color.training {
  background-color: #007bff;
  /* Blue */
}

.legend-color.career {
  background-color: #FFD43B;
  /* Yellow */
}

/* Make it responsive */
@media (max-width: 900px) {
  .calendar-legend {
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.85rem;
  }
}


/* Responsive Design */
@media (max-width: 900px) {
  .calendar-container {
    flex-direction: column;
    /* stack vertically */
  }

  .calendar-wrapper,
  .calendar-side {
    width: 100%;
  }

  .calendar-side {
    margin-top: 1rem;
  }
}
</style>