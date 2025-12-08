<template>
  <div class="organization-homepage" :class="{ 'sidebar-expanded': isSidebarOpen }">

    <!-- Mobile Backdrop - only show when sidebar is open (expanded) -->
    <div v-if="isSidebarOpen" class="mobile-backdrop" @click="toggleSidebar"></div>

    <!-- Sidebar -->
    <transition name="slide">
      <aside class="sidebar" :class="{ collapsed: !isSidebarOpen }" @click.stop>
        <!-- Hamburger Toggle inside sidebar -->
        <button class="hamburger" @click="toggleSidebar" :class="{ open: isSidebarOpen }">
          <span></span>
          <span></span>
          <span></span>
        </button>

        <div class="space">

        </div>
        <!-- Avatar always visible -->
        <div class="avatar" @click.stop>
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
              <div class="action" @click.stop="navigateTo({ name: 'OrgUpdateProfile' })">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M11.7278 8.27191C12.7534 9.87525 14.1247 11.2375 15.7464 12.2534L8.85673 19.144C8.43166 19.569 8.21841 19.7816 7.95731 19.9213C7.69637 20.0609 7.40171 20.1199 6.81278 20.2377L3.73563 20.853C3.40302 20.9195 3.23649 20.9525 3.14188 20.8578C3.04759 20.7632 3.08035 20.5971 3.14677 20.2651L3.76298 17.1879C3.88087 16.5985 3.93965 16.3035 4.07938 16.0424C4.21912 15.7814 4.43173 15.569 4.85673 15.144L11.7278 8.27191ZM16.1116 4.03656C16.6711 3.75929 17.3284 3.75931 17.888 4.03656C18.1821 4.18229 18.455 4.45518 19.0003 5.00043C19.5453 5.54545 19.8184 5.81774 19.9641 6.11175C20.2414 6.67123 20.2413 7.32861 19.9641 7.88812C19.8184 8.18221 19.5455 8.45517 19.0003 9.00043L17.2034 10.7963C15.5308 9.84498 14.1456 8.46859 13.1819 6.81781L15.0003 5.00043C15.5453 4.45539 15.8176 4.18234 16.1116 4.03656Z"
                    fill="#FFFDFD" />
                </svg>
                <span>Update Profile</span>
              </div>
              <div class="action" @click.stop="navigateTo({ name: 'OrgChangePassword' })">
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

        <div class="icon" @click.stop="navigateTo('/organization')">
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
        <div class="icon" @click.stop="navigateTo({ name: 'OrgTrainings' })">
          <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M20.5837 3.625C23.4119 3.625 24.8261 3.62526 25.7048 4.50391C26.5833 5.3826 26.5837 6.79675 26.5837 9.625V19.375C26.5837 22.2033 26.5833 23.6174 25.7048 24.4961C24.8261 25.3747 23.4119 25.375 20.5837 25.375H8.41666C5.58824 25.375 4.17425 25.3748 3.29557 24.4961C2.41689 23.6174 2.41666 22.2034 2.41666 19.375V9.625C2.41666 6.79657 2.41689 5.38259 3.29557 4.50391C4.17425 3.62523 5.58824 3.625 8.41666 3.625H20.5837ZM9.66666 12.292C9.11438 12.292 8.66666 12.7397 8.66666 13.292V20.542L8.67155 20.6445C8.72303 21.1485 9.1491 21.542 9.66666 21.542C10.1842 21.542 10.6103 21.1485 10.6618 20.6445L10.6667 20.542V13.292C10.6667 12.7397 10.2189 12.292 9.66666 12.292ZM19.3337 9.875C18.7814 9.875 18.3337 10.3227 18.3337 10.875V20.542L18.3385 20.6436C18.3896 21.148 18.8158 21.542 19.3337 21.542C19.8514 21.5418 20.2778 21.1479 20.3288 20.6436L20.3337 20.542V10.875C20.3337 10.3228 19.8858 9.87518 19.3337 9.875ZM14.4997 14.708C13.9476 14.7082 13.4998 15.156 13.4997 15.708V20.541L13.5046 20.6436C13.5557 21.1477 13.982 21.5408 14.4997 21.541C15.0175 21.541 15.4436 21.1478 15.4948 20.6436L15.4997 20.541V15.708C15.4995 15.1559 15.0518 14.708 14.4997 14.708Z"
              fill="white" />
          </svg>
          <span>Trainings</span>
        </div>
        <div class="icon" @click.stop="navigateTo({ name: 'OrgCareers' })">
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
        <div class="icon" @click.stop="navigateTo({ name: 'OrgCalendar' })">
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
        <div class="icon signout" @click.stop="logout">
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
          <img :src="pathfinderLogo" alt="Pathfinder Logo" class="pathfinder-logo" />
          <span class="logo-text">Pathfinder</span>
        </div>
      </header>

      <!-- Verification Warning Card -->
      <div v-if="!isOrganizationVerified" class="verification-warning-card">
        <div class="verification-warning-content">
          <svg class="verification-warning-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
            <path d="M12 9v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
          </svg>
          <div class="verification-warning-text">
            <p class="verification-warning-title">Account not verified by admin.</p>
            <p class="verification-warning-email">
              Contact admin at: <span class="admin-email">admin@pathfinder.com</span>
            </p>
          </div>
        </div>
      </div>

      <!-- 🧾 Summary Cards -->
      <section class="cards-container">
        <div class="card">
          <h4 class="cards-title">Total Trainings</h4>
          <span class="cards-counter">{{ totalTrainings }}</span>
        </div>
        <div class="card">
          <h4 class="cards-title">Upcoming Trainings</h4>
          <span class="cards-counter">{{ upcomingTrainings }}</span>
        </div>
        <div class="card">
          <h4 class="cards-title">On-Going Trainings</h4>
          <span class="cards-counter">{{ ongoingTrainings }}</span>
        </div>
        <div class="card">
          <h4 class="cards-title">Completed Trainings</h4>
          <span class="cards-counter">{{ completedTrainings }}</span>
        </div>
        <div class="card">
          <h4 class="cards-title">Total Careers</h4>
          <span class="cards-counter">{{ totalCareers }}</span>
        </div>
        <div class="card">
          <h4 class="cards-title">Open Careers</h4>
          <span class="cards-counter">{{ ongoingCareers }}</span>
        </div>
        <div class="card">
          <h4 class="cards-title">Closed Careers</h4>
          <span class="cards-counter">{{ filledOutCareers }}</span>
        </div>
      </section>

      <!-- Chart Section -->
      <section class="charts-container relative">

      <!-- ONE GLOBAL LOADER -->
      <div
        v-if="isChartsLoading"
        class="absolute inset-0 flex items-center justify-center bg-white/70 backdrop-blur-sm rounded-lg z-20"
      >
        <svg
          class="animate-spin h-14 w-14 text-blue-600"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v8H4z"
          ></path>
        </svg>
      </div>

      <!-- MAIN CHART -->
      <div
        class="main-chart"
        :class="isChartsLoading ? 'opacity-0' : 'opacity-100 transition-opacity duration-300'"
      >
        <canvas :id="activeChart.id"></canvas>
        <h3>{{ activeChart.title }}</h3>
      </div>

      <!-- SIDE CHARTS -->
      <div
        class="side-charts"
        :class="isChartsLoading ? 'opacity-0' : 'opacity-100 transition-opacity duration-300'"
      >
        <div
          v-for="chart in sideCharts"
          :key="chart.id"
          class="side-chart cursor-pointer"
          @click="swapChart(chart)"
        >
          <canvas :id="chart.id"></canvas>
          <h4>{{ chart.title }}</h4>
        </div>
      </div>

    </section>

    </main>
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import { getImageUrl } from "@/lib/supabase.js";
import pathfinderLogo from "@/assets/images/Pathfinder_Logo.png";

export default {
  name: "OrganizationHomePage",

  data() {
    return {
    };
  },

  setup() {
    // Load sidebar state from localStorage, default to true for first visit
    const savedSidebarState = localStorage.getItem('orgSidebarOpen');
    const isSidebarOpen = ref(savedSidebarState !== null ? savedSidebarState === 'true' : true);

    const organizationName = ref("Loading...");
    const organizationLogo = ref(null);

    // Computed property for logo URL - use organization logo if available
    const logoUrl = computed(() => {
      if (organizationLogo.value) {
        const logoPath = organizationLogo.value.logo_directory || organizationLogo.value.Logo_directory || organizationLogo.value.logoPath;
        if (logoPath) {
          const url = getImageUrl(logoPath, "Requirements");
          return url || null;
        }
      }
      return null;
    });

    onMounted(() => {
      const storedName = localStorage.getItem("organizationName");
      organizationName.value = storedName || "My Organization";
      
      // Get organization logo from localStorage
      const storedUser = localStorage.getItem("user");
      if (storedUser) {
        const user = JSON.parse(storedUser);
        if (user.organization) {
          organizationLogo.value = user.organization;
        } else if (user.logo_directory || user.Logo_directory || user.logoPath) {
          organizationLogo.value = user;
        }
      }
    });

    // Functions
    const toggleSidebar = () => {
      isSidebarOpen.value = !isSidebarOpen.value;
      // Save state to localStorage
      localStorage.setItem('orgSidebarOpen', isSidebarOpen.value.toString());
    };

    return {
      logoUrl,
      organizationLogo,
      pathfinderLogo,
    };
  },
};
</script>


<script setup>
import { ref, nextTick, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import Chart from 'chart.js/auto';
import api from '@/composables/api';
import { getImageUrl } from "@/lib/supabase.js";

// ----------------------
// Router & Sidebar
// ----------------------
const router = useRouter();
// Load sidebar state from localStorage, default to true for first visit
const savedSidebarState = localStorage.getItem('orgSidebarOpen');
const isSidebarOpen = ref(savedSidebarState !== null ? savedSidebarState === 'true' : true);
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
  // Save state to localStorage
  localStorage.setItem('orgSidebarOpen', isSidebarOpen.value.toString());
};

// ----------------------
// Loading States for Charts
// ----------------------
const isChartsLoading = ref(true);

// Each chart has its own loading state
const isChartLoading = ref({
  chart1: true,
  chart2: true,
  chart3: true,
  chart4: true
});

// ----------------------
// Organization & User Info
// ----------------------
const organizationName = ref('Loading...');
const organizationLogo = ref(null);
const organizationStatus = ref(null);
const isOrganizationVerified = ref(false);

// Computed property for logo URL - use organization logo if available
const logoUrl = computed(() => {
  if (organizationLogo.value) {
    const logoPath = organizationLogo.value.logo_directory || organizationLogo.value.Logo_directory || organizationLogo.value.logoPath;
    if (logoPath) {
      const url = getImageUrl(logoPath, "Requirements");
      return url || null;
    }
  }
  return null;
});

onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    const user = JSON.parse(storedUser);
    organizationName.value = user.organizationName || user.displayName || user.name || "Unknown Org";
    
    // Get organization logo
    if (user.organization) {
      organizationLogo.value = user.organization;
    } else if (user.logo_directory || user.Logo_directory || user.logoPath) {
      organizationLogo.value = user;
    }
    
    // Get organization status
    organizationStatus.value = user.status || user.Status || null;
    isOrganizationVerified.value = ["approved", "verified"].includes(
      (organizationStatus.value || "").toString().toLowerCase()
    );
  }
});

// Computed properties for verification status
const organizationStatusLabel = computed(() => {
  return isOrganizationVerified.value ? "Verified" : "Unverified";
});

const organizationStatusClass = computed(() => {
  return isOrganizationVerified.value ? "org-status-verified" : "org-status-unverified";
});

// ----------------------
// Counters / Totals
// ----------------------
const totalTrainings = ref(0);
const upcomingTrainings = ref(0);
const ongoingTrainings = ref(0);
const completedTrainings = ref(0);

const totalCareers = ref(0);
const ongoingCareers = ref(0);
const filledOutCareers = ref(0);

const trainingsData = ref([]);
const careersData = ref([]);
const registrationsData = ref([]);
const applicationsData = ref([]);

// Helper function to parse local datetime (similar to OrganizationTrainings.vue)
const parseLocalDateTime = (dateString) => {
  if (!dateString) return null;
  
  try {
    // Split by space or T to get date and time parts
    const [datePart, timePart] = dateString.split(/[ T]/);
    if (!datePart) return null;
    
    const [year, month, day] = datePart.split("-");
    const timeStr = timePart || "00:00:00";
    const [hour, minute, second] = timeStr.split(":");
    
    // Create date in local timezone (not UTC)
    return new Date(
      Number(year),
      Number(month) - 1, // Month is 0-indexed
      Number(day),
      Number(hour || 0),
      Number(minute || 0),
      Number(second || 0)
    );
  } catch (error) {
    console.error("Error parsing date:", dateString, error);
    return null;
  }
};

// Check if a training is currently ongoing
const isTrainingOngoing = (training) => {
  if (!training) return false;
  
  const now = new Date();
  
  // Check if training has multiple schedules
  if (training.schedules && Array.isArray(training.schedules) && training.schedules.length > 0) {
    // Check if any schedule is currently active
    return training.schedules.some(schedule => {
      const scheduleTime = schedule.schedule || schedule.Schedule;
      const endTime = schedule.end_time || schedule.endTime;
      
      if (!scheduleTime || !endTime) return false;
      
      const startTime = parseLocalDateTime(scheduleTime);
      const endTimeDate = parseLocalDateTime(endTime);
      
      if (!startTime || !endTimeDate) return false;
      
      return now >= startTime && now < endTimeDate;
    });
  }
  
  // Single schedule format (backward compatibility)
  const scheduleTime = training.schedule || training.Schedule;
  const endTime = training.end_time || training.endTime;
  
  if (!scheduleTime || !endTime) return false;
  
  const startTime = parseLocalDateTime(scheduleTime);
  const endTimeDate = parseLocalDateTime(endTime);
  
  if (!startTime || !endTimeDate) return false;
  
  return now >= startTime && now < endTimeDate;
};

const fetchTrainingStats = async () => {
  try {
    let data;
    try {
      ({ data } = await api.get("/organization/trainings"));
    } catch (err) {
      console.warn("Org trainings endpoint unavailable for homepage, falling back:", err?.response?.status);
      const storedUser = localStorage.getItem("user");
      const parsedUser = storedUser ? JSON.parse(storedUser) : null;
      const organizationID = parsedUser?.organizationID ?? parsedUser?.organization?.organizationID ?? null;
      ({ data } = await api.get("/trainings", {
        params: organizationID ? { organizationID } : {},
      }));
    }

    trainingsData.value = Array.isArray(data) ? data : [];
    const now = new Date();

    totalTrainings.value = trainingsData.value.length;
    
    // Count ongoing trainings (currently happening)
    ongoingTrainings.value = trainingsData.value.filter(t => isTrainingOngoing(t)).length;
    
    // Count upcoming trainings (not started yet, not ongoing)
    upcomingTrainings.value = trainingsData.value.filter(t => {
      // Exclude ongoing trainings
      if (isTrainingOngoing(t)) return false;
      
      // Check if training has multiple schedules
      if (t.schedules && Array.isArray(t.schedules) && t.schedules.length > 0) {
        // Check if there's at least one schedule that hasn't started yet
        return t.schedules.some(schedule => {
          const scheduleTime = schedule.schedule || schedule.Schedule;
          if (!scheduleTime) return false;
          const startTime = parseLocalDateTime(scheduleTime);
          return startTime && startTime.getTime() > now.getTime();
        });
      }
      
      // Single schedule format - check if it hasn't started yet
      const scheduleTime = t.schedule || t.Schedule;
      if (!scheduleTime) return false;
      const startTime = parseLocalDateTime(scheduleTime);
      return startTime && startTime.getTime() > now.getTime();
    }).length;
    
    // Completed trainings = total - ongoing - upcoming
    completedTrainings.value = totalTrainings.value - ongoingTrainings.value - upcomingTrainings.value;
  } catch (err) {
    console.error("Failed to fetch training stats:", err);
    trainingsData.value = [];
  }
};

const fetchCareerStats = async () => {
  try {
    const { data } = await api.get("/organization/careers");
    careersData.value = Array.isArray(data) ? data : [];
    
    // Normalize today to midnight for date-only comparison
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    totalCareers.value = careersData.value.length;
    ongoingCareers.value = careersData.value.filter(c => {
      // Check both closingDate and deadlineOfSubmission fields
      const deadlineStr = c.closingDate || c.deadlineOfSubmission;
      if (!deadlineStr) return true; // If no deadline, consider it open
      
      const deadline = new Date(deadlineStr);
      if (isNaN(deadline.getTime())) return true; // Invalid date, consider it open
      
      // Normalize deadline to midnight for date-only comparison
      deadline.setHours(0, 0, 0, 0);
      return deadline >= today; // Career is open if deadline is today or in the future
    }).length;
    filledOutCareers.value = totalCareers.value - ongoingCareers.value;
  } catch (err) {
    console.error("Failed to fetch career stats:", err);
    careersData.value = [];
  }
};

// ----------------------
// Charts
// ----------------------
const charts = ref({});
const activeChart = ref({ id: "chart1", title: "General Overview" });
const sideCharts = ref([
  { id: "chart2", title: "Training Summary" },
  { id: "chart3", title: "Applicant Engagement" },
  { id: "chart4", title: "Career Insights" },
]);

// Chart configurations
const chartConfigs = ref({
  chart1: {
    type: "line",
    data: {
      labels: [], datasets: [
        { label: "Training Registrants", data: [], borderColor: "#44576D", backgroundColor: "rgba(68,87,109,0.2)", tension: 0.3, fill: true },
        { label: "Career Applicants", data: [], borderColor: "#A7B1C2", backgroundColor: "rgba(167,177,194,0.2)", tension: 0.3, fill: true }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        }
      }
    }
  },
  chart2: {
    type: "pie",
    data: { labels: ["On-site", "Online"], datasets: [{ data: [], backgroundColor: ["#44576D", "#A7B1C2"] }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom'
        }
      }
    }
  },
  chart3: {
    type: "bar",
    data: { labels: ["Careers", "Trainings"], datasets: [{ label: "Engagement", data: [], backgroundColor: "#44576D" }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  },
  chart4: {
    type: "doughnut",
    data: { labels: ["Submitted", "In Review", "For Interview", "Accepted", "Rejected"], datasets: [{ data: [], backgroundColor: ["#44576D", "#A7B1C2", "#DFE3EA", "#8FA5B3", "#6B7D8F"] }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom'
        }
      }
    }
  }
});

// Fetch registrations and applications for chart data
const fetchRegistrations = async () => {
  try {
    // For organizations, we need to get registrations for all their trainings
    const allRegistrations = [];
    for (const training of trainingsData.value) {
      try {
        const { data } = await api.get(`/trainings/${training.trainingID}/registrants`);
        if (Array.isArray(data)) {
          // Add trainingID and registrationDate to each registration for chart calculations
          data.forEach(reg => {
            allRegistrations.push({
              ...reg,
              trainingID: training.trainingID,
              registrationDate: reg.dateRegistered || reg.registrationDate
            });
          });
        }
      } catch (err) {
        // Skip if we can't get registrants for this training
        console.warn(`Failed to fetch registrants for training ${training.trainingID}:`, err);
      }
    }
    registrationsData.value = allRegistrations;
  } catch (err) {
    console.warn("Failed to fetch registrations:", err);
    registrationsData.value = [];
  }
};

const fetchApplications = async () => {
  try {
    // For organizations, we need to get applications for all their careers
    const allApplications = [];
    for (const career of careersData.value) {
      try {
        const { data } = await api.get(`/careers/${career.careerID}/applicants`);
        if (Array.isArray(data)) {
          // Add careerID and dateSubmitted to each application for chart calculations
          data.forEach(app => {
            allApplications.push({
              ...app,
              careerID: career.careerID,
              dateSubmitted: app.dateSubmitted,
              applciationStatus: app.status || app.applicationStatus || app.applciationStatus
            });
          });
        }
      } catch (err) {
        // Skip if we can't get applicants for this career
        console.warn(`Failed to fetch applicants for career ${career.careerID}:`, err);
      }
    }
    applicationsData.value = allApplications;
  } catch (err) {
    console.warn("Failed to fetch applications:", err);
    applicationsData.value = [];
  }
};

// Calculate chart data from fetched data
const calculateChartData = () => {
  // Chart 1: General Overview - Full year (12 months: January to December)
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const currentYear = new Date().getFullYear();
  
  const monthData = months.map((_, index) => {
    const monthNum = index + 1; // 1-12 for Jan-Dec
    
    const registrants = registrationsData.value.filter(r => {
      if (!r.registrationDate && !r.dateRegistered) return false;
      const dateStr = r.registrationDate || r.dateRegistered;
      if (!dateStr) return false;
      const regDate = new Date(dateStr);
      if (isNaN(regDate.getTime())) return false;
      return regDate.getMonth() + 1 === monthNum && regDate.getFullYear() === currentYear;
    }).length;
    
    const applicants = applicationsData.value.filter(a => {
      if (!a.dateSubmitted) return false;
      const appDate = new Date(a.dateSubmitted);
      if (isNaN(appDate.getTime())) return false;
      return appDate.getMonth() + 1 === monthNum && appDate.getFullYear() === currentYear;
    }).length;
    
    return { registrants, applicants };
  });
  
  chartConfigs.value.chart1.data.labels = months;
  chartConfigs.value.chart1.data.datasets[0].data = monthData.map(m => m.registrants);
  chartConfigs.value.chart1.data.datasets[1].data = monthData.map(m => m.applicants);

  // Chart 2: Training Summary - Count by mode (On-site and Online only)
  const trainingModes = {
    onsite: trainingsData.value.filter(t => {
      const mode = (t.mode || t.Mode || '').toLowerCase();
      return mode === 'onsite' || mode === 'on-site' || mode === 'on site';
    }).length,
    online: trainingsData.value.filter(t => {
      const mode = (t.mode || t.Mode || '').toLowerCase();
      return mode === 'online';
    }).length
  };
  
  chartConfigs.value.chart2.data.datasets[0].data = [
    trainingModes.onsite,
    trainingModes.online
  ];

  // Chart 3: Applicant Engagement - unique applicants vs registrants
  const uniqueApplicants = new Set(
    applicationsData.value.map(a => a.applicantID).filter(Boolean)
  );
  const uniqueRegistrants = new Set(
    registrationsData.value.map(r => r.applicantID || r.id).filter(Boolean)
  );

  chartConfigs.value.chart3.data.datasets[0].data = [
    uniqueApplicants.size,
    uniqueRegistrants.size
  ];

  // Chart 4: Career Insights - Application statuses: Submitted, In Review, For Interview, Accepted, Rejected
  const outcomes = {
    submitted: applicationsData.value.filter(a => {
      const status = (a.applciationStatus || a.applicationStatus || a.status || '').toLowerCase();
      return status === 'submitted' || status === '';
    }).length,
    inReview: applicationsData.value.filter(a => {
      const status = (a.applciationStatus || a.applicationStatus || a.status || '').toLowerCase();
      return status === 'in review' || status === 'pending';
    }).length,
    forInterview: applicationsData.value.filter(a => {
      const status = (a.applciationStatus || a.applicationStatus || a.status || '').toLowerCase();
      return status === 'for interview';
    }).length,
    accepted: applicationsData.value.filter(a => {
      const status = (a.applciationStatus || a.applicationStatus || a.status || '').toLowerCase();
      return status === 'accepted';
    }).length,
    rejected: applicationsData.value.filter(a => {
      const status = (a.applciationStatus || a.applicationStatus || a.status || '').toLowerCase();
      return status === 'rejected';
    }).length
  };
  
  chartConfigs.value.chart4.data.datasets[0].data = [
    outcomes.submitted,
    outcomes.inReview,
    outcomes.forInterview,
    outcomes.accepted,
    outcomes.rejected
  ];
};

// Calculate and render chart data from database
const fetchChartData = async () => {
  // All charts now depend entirely on database data (trainingsData, careersData, registrationsData, applicationsData)
  calculateChartData();
  
  // Re-render all charts after data update
  await nextTick();
  renderChart(activeChart.value.id);
  sideCharts.value.forEach(chart => renderChart(chart.id));
};


// Render a single chart
function renderChart(id) {
  const ctx = document.getElementById(id);
  if (!ctx) {
    console.warn(`Canvas element with id "${id}" not found`);
    return;
  }

  // Ensure loading state ON while rendering
  isChartLoading.value[id] = true;

  // Destroy previous chart instance
  if (charts.value[id]) {
    charts.value[id].destroy();
  }

  const config = chartConfigs.value[id];
  if (!config) {
    console.warn(`Chart config for "${id}" not found`);
    return;
  }

  // Render new chart
  charts.value[id] = new Chart(ctx, config);

  // Stop loading after small delay (smooth UX)
  setTimeout(() => {
    isChartLoading.value[id] = false;

    // If all charts finished loading, disable global loading
    const allDone = Object.values(isChartLoading.value).every(v => v === false);
    if (allDone) isChartsLoading.value = false;

  }, 300); // delay for smooth fade-in
}

// Swap active and side charts
function swapChart(clickedChart) {

  // Reset loading state
  isChartLoading.value[clickedChart.id] = true;

  const oldMain = { ...activeChart.value };
  activeChart.value = clickedChart;

  sideCharts.value = sideCharts.value.map(c =>
    c.id === clickedChart.id ? oldMain : c
  );

  nextTick(() => {
    Object.values(charts.value).forEach(chart => chart.destroy());
    charts.value = {};

    renderChart(activeChart.value.id);
    sideCharts.value.forEach(c => {
      isChartLoading.value[c.id] = true;
      renderChart(c.id);
    });
  });
}

// ----------------------
// Lifecycle
// ----------------------
onMounted(async () => {
  isChartsLoading.value = true;

  await Promise.all([
    fetchTrainingStats(),
    fetchCareerStats()
  ]);

  await Promise.all([
    fetchRegistrations(),
    fetchApplications()
  ]);

  await fetchChartData(); // render charts inside this

  // All data loaded; charts will switch loading off after render
});

// ----------------------
// Navigation
// ----------------------
const goToProfile = () => router.push('/profile');
const goToHome = () => router.push('/organization');
const goToTrainings = () => router.push({ name: 'OrgTrainings' });
const goToCareers = () => router.push({ name: 'OrgCareers' });
const goToCalendar = () => router.push('/app/calendar');
const navigateTo = (route) => router.push(route);

// ----------------------
// Logout
// ----------------------
const logout = () => {
  localStorage.removeItem('user');
  localStorage.removeItem('token');
  router.push({ name: 'Login' });
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

.organization-homepage {
  display: flex;
  height: 100vh;
  font-family: 'Poppins', sans-serif;
  background-color: #f4f4f4;
  position: relative;
}

/* Mobile backdrop overlay */
.mobile-backdrop {
  display: none;
}

@media (max-width: 768px) {
  /* Only show backdrop when sidebar is open (expanded), not when collapsed */
  .mobile-backdrop {
    display: none;
  }

  /* Show backdrop only when sidebar is expanded */
  .organization-homepage.sidebar-expanded .mobile-backdrop {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    transition: opacity 0.3s ease-in-out;
  }
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
  transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
  box-sizing: border-box;
}

.content > header.topbar {
  margin-left: 7.5px;
  margin-right: -40px;
  padding-left: 40px;
  padding-right: 40px;
}

.topbar {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 10px;
  margin-top: -30px;
  width: 100%;
  position: relative;
}

.logo-title {
  font-size: 32px;
  font-weight: 700;
  color: #2d3748;
}

.Pathfinder-wrapper {
  display: flex;
  align-items: center;
  gap: 0;
  width: fit-content;
  position: relative;
}

.pathfinder-logo {
  height: 135px;
  width: 135px;
  object-fit: contain;
}

.logo-text {
  font-size: 26px;
  font-weight: 700;
  color: #44576D;
  font-family: 'Poppins', sans-serif;
  margin-left: -25px;
}

/* Verification Warning Card */
.verification-warning-card {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border: 2px solid #f59e0b;
  border-radius: 12px;
  padding: 20px 24px;
  margin-bottom: 30px;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.verification-warning-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.verification-warning-icon {
  width: 32px;
  height: 32px;
  color: #d97706;
  flex-shrink: 0;
}

.verification-warning-text {
  flex: 1;
}

.verification-warning-title {
  font-size: 16px;
  font-weight: 600;
  color: #92400e;
  margin: 0 0 6px 0;
}

.verification-warning-email {
  font-size: 14px;
  color: #78350f;
  margin: 0;
}

.admin-email {
  font-weight: 600;
  color: #d97706;
  text-decoration: underline;
  cursor: pointer;
}

.admin-email:hover {
  color: #b45309;
}

.chart-section {
  background: white;
  padding: 30px;
  border-radius: 16px;
  margin: 40px auto;
  width: 90%;
  max-width: 900px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.chart-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 15px;
  color: #2d3748;
  text-align: center;
}

.chart-section canvas {
  width: 100% !important;
  max-width: 800px;
  height: auto !important;
  margin: 0 auto;
}

.legend {
  display: flex;
  justify-content: center;
  gap: 30px;
  margin-top: 10px;
  font-size: 14px;
  color: #4a5568;
}

.dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 6px;
}

.dot.blue {
  background-color: #3182ce;
}

.dot.purple {
  background-color: #9f7aea;
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
  width: 400px;
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

/* Placeholder / empty text */
.schedule-input-wrapper input[type="date"]::placeholder {
  color: #666;
  /* same as other textboxes */
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

/* Hamburger inside sidebar */
.hamburger {
  width: 30px;
  height: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px 4px;
  margin: 15px auto 10px auto;
  border-radius: 6px;
  transition: all 0.3s ease;
  align-self: center;
  position: relative;
}

/* When sidebar is open, move hamburger to the right */
.sidebar:not(.collapsed) .hamburger {
  align-self: flex-end;
  margin: 15px 10px 10px auto;
}

.hamburger:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

/* Hamburger lines - always stay as 3 lines */
.hamburger span {
  display: block;
  height: 3px;
  width: 100%;
  background-color: white;
  border-radius: 2px;
  flex-shrink: 0;
}

/* 🧩 Base Layout */
.charts-container {
  display: flex;
  gap: 20px;
  padding: 20px;
  align-items: stretch;
  /* ✅ Ensures both columns are equal height */
  box-sizing: border-box;
}

/* 🟦 Main Chart */
.main-chart {
  flex: 3;
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  text-align: center;

  height: 70vh;
  max-height: 700px;
  min-height: 500px;

  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
}

/* 🟨 Side Charts Container */
.side-charts {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-content: flex-start;
  height: 70vh;
  /* ✅ Exactly same height as main */
  max-height: 700px;
  min-height: 500px;
  gap: 15px;
  /* ✅ Controlled spacing between side charts */
  box-sizing: border-box;
}

/* 🟩 Each Side Chart */
.side-chart {
  background: white;
  border-radius: 12px;
  padding: 15px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  cursor: pointer;

  /* ✅ Flex math: each chart + gaps = full container height */
  flex: 1 1 0;
  min-height: 0;
  /* avoids overflow */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;

  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.side-chart:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

/* 🧭 Chart canvases */
canvas {
  width: 100% !important;
  height: 100% !important;
  object-fit: contain;
}

/* 🏷️ Titles */
.main-chart h3,
.side-chart h4 {
  margin-top: 10px;
  color: #44576d;
  font-weight: 600;
  font-size: 1rem;
}

/* 📱 Responsive Adjustments */

/* Medium Screens (Tablets, Laptops < 1200px) */
@media (max-width: 1200px) {
  .charts-container {
    flex-direction: column;
    align-items: center;
  }

  .main-chart {
    flex: none;
    width: 100%;
    height: 550px;
  }

  .side-charts {
    flex: none;
    width: 100%;
    height: auto;
    flex-direction: row;
    justify-content: space-between;
    gap: 10px;
  }

  .side-chart {
    flex: 1;
    height: 250px;
  }
}

/* Small Screens (Tablets < 768px) */
@media (max-width: 768px) {
  .charts-container {
    flex-direction: column;
    padding: 15px;
    gap: 15px;
  }

  .main-chart {
    height: 400px;
    padding: 20px;
  }

  .side-charts {
    flex-direction: column;
    gap: 15px;
    height: auto;
  }

  .side-chart {
    height: 220px;
  }
}

/* Extra Small Screens (Phones < 480px) */
@media (max-width: 480px) {
  .charts-container {
    padding: 10px;
    gap: 10px;
  }

  .main-chart {
    height: 350px;
    padding: 15px;
  }

  .side-chart {
    height: 200px;
    padding: 12px;
  }

  .main-chart h3,
  .side-chart h4 {
    font-size: 0.9rem;
  }
}

/* 🧾 Summary Cards Container */
.cards-container {
  display: flex;
  justify-content: space-between;
  align-items: stretch;
  gap: 20px;
  margin: 20px;
  flex-wrap: wrap;
  /* allow wrapping on smaller screens */
}

/* 🏷️ Titles & Text */
.cards-title {
  font-size: 14px;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 12px 0;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1.4;
}

.cards-counter {
  font-size: 30px;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
}

.card {
  flex: 1;
  background: white;
  border-radius: 12px;
  padding: 18px;
  text-align: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 120px;
}


/* 📱 Responsive */
@media (max-width: 768px) {
  .cards-container {
    flex-direction: column;
    gap: 15px;
    margin: 15px;
  }

  .summary-card {
    width: 100%;
  }

  /* Sidebar on mobile - show collapsed state (icons only) when not open */
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    z-index: 1000;
    transition: width 0.3s ease-in-out;
  }

  /* When collapsed on mobile, show 60px width (icons only) like desktop */
  .sidebar.collapsed {
    width: 60px;
    transform: translateX(0);
  }

  /* When open on mobile, show full width with backdrop */
  .sidebar:not(.collapsed) {
    width: 230px;
    transform: translateX(0);
  }

  /* Content padding on mobile - account for sidebar */
  .content {
    padding: 20px 15px;
    width: calc(100% - 60px);
    margin-left: 60px; /* Space for collapsed sidebar (60px) */
    transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
  }

  /* When sidebar is open (expanded), adjust content */
  .organization-homepage.sidebar-expanded .content {
    width: calc(100% - 230px);
    margin-left: 230px;
  }

  /* Hamburger positioning on mobile */
  .hamburger {
    margin: 15px auto 10px auto;
    align-self: center;
  }

  /* When sidebar is open on mobile, move hamburger to the right */
  .sidebar:not(.collapsed) .hamburger {
    align-self: flex-end;
    margin: 15px 10px 10px auto;
  }

  /* Topbar adjustments */
  .topbar {
    margin-bottom: 20px;
  }

  .logo-text {
    font-size: 22px;
  }

  /* Cards responsive */
  .card {
    min-height: 100px;
    padding: 15px;
  }

  .cards-title {
    font-size: 12px;
    min-height: 35px;
  }

  .cards-counter {
    font-size: 24px;
  }

  /* Verification warning card */
  .verification-warning-card {
    padding: 15px 18px;
    margin-bottom: 20px;
  }

  .verification-warning-title {
    font-size: 14px;
  }

  .verification-warning-email {
    font-size: 12px;
  }
}

/* Extra Small Screens (Phones < 480px) */
@media (max-width: 480px) {
  .content {
    padding: 15px 10px;
  }

  .cards-container {
    margin: 10px;
    gap: 10px;
  }

  .card {
    padding: 12px;
    min-height: 90px;
  }

  .cards-title {
    font-size: 11px;
    min-height: 30px;
  }

  .cards-counter {
    font-size: 20px;
  }

  .logo-text {
    font-size: 20px;
  }

  .verification-warning-card {
    padding: 12px 15px;
  }
}

/* Large screens - ensure sidebar doesn't overlay */
@media (min-width: 769px) {
  .sidebar {
    position: relative;
  }

  .content {
    margin-left: 0;
  }
}
</style>
