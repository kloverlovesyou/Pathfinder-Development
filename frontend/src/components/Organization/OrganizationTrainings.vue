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
          <div class="relative w-full sm:w-3/4 md:w-1/2 lg:w-1/3">
            <input type="text" v-model="globalSearchQuery" placeholder="Search trainings..."
              class="global-search-bar text-black px-4 py-2 border rounded-lg w-full" maxlength="100" />
            <span class="char-counter-search">{{ (globalSearchQuery || '').length }}/100</span>
          </div>
        </div>
      </section>

      <section class="upcoming">
        <div class="flex items-center justify-between">
          <h2 class="section-title flex items-center gap-1">
            Upcoming Trainings
            <span class="count-badge">{{ sortedUpcomingTrainings.length }}</span>
          </h2>

          <button class="plus-btn-text" @click="openTrainingPopup()" :disabled="!isOrganizationVerified"
            :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''">+</button>
        </div>

        <!-- ✅ Loader -->
        <div v-if="isLoading" class="flex justify-center py-8">
          <svg
            class="animate-spin h-10 w-10 text-blue-600"
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

        <!-- ✅ Grid Layout -->
        <div v-else class="trainings-grid">
          <div v-for="training in visibleFilteredUpcoming" :key="training.trainingID" class="training-card"
            @click="openTrainingDetails(training)">
            <div class="training-right">
              <h3 class="training-title">{{ training.title }}</h3>
              <p class="training-date">{{ formatSchedule(training.schedule) }}</p>
            </div>

            <!-- Menu -->
            <div class="menu">
              <div class="menu-icon" @click.stop="toggleUpcomingMenu(training.trainingID)">⋮</div>
              <div v-if="openUpcomingMenu === training.trainingID" class="dropdown-menu" @click.stop>
                <ul>
                  <li @click="deleteTraining(training.trainingID)"
                      :class="{ 'disabled-action': !isOrganizationVerified }"
                      :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''">
                    Delete Training
                  </li>
                  <li @click="updateTraining(training.trainingID)"
                      :class="{ 'disabled-action': !isOrganizationVerified }"
                      :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''">
                    Update Training
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Show More Button -->
        <button v-if="sortedUpcomingTrainings.length > 4 && !isLoading" class="show-more-btn"
          @click="showAllUpcoming = !showAllUpcoming">
          {{ showAllUpcoming ? 'Show Less' : 'Show More' }}
        </button>
      </section>

      <!-- ✅ On-going Trainings Section -->
      <section class="ongoing">
        <div class="flex items-center justify-between">
          <h2 class="section-title flex items-center gap-1">
            On-going Trainings
            <span class="count-badge">{{ sortedOngoingTrainings.length }}</span>
          </h2>
        </div>

        <!-- ✅ Loader -->
        <div v-if="isLoading" class="flex justify-center py-8">
          <svg
            class="animate-spin h-10 w-10 text-blue-600"
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

        <!-- ✅ Grid Layout -->
        <div v-else class="trainings-grid">
          <div v-for="training in visibleFilteredOngoing" :key="training.trainingID" class="training-card"
            @click="openTrainingDetails(training)">
            <div class="training-right">
              <h3 class="training-title">{{ training.title }}</h3>
              <p class="training-date">{{ formatSchedule(training.schedule) }}</p>
              <span v-if="isTrainingOngoing(training)" class="badge-ongoing">Ongoing</span>
            </div>

            <!-- Menu -->
            <div class="menu">
              <div class="menu-icon" @click.stop="toggleOngoingMenu(training.trainingID)">⋮</div>
              <div v-if="openOngoingMenu === training.trainingID" class="dropdown-menu" @click.stop>
                <ul>
                  <li @click="deleteTraining(training.trainingID)"
                      :class="{ 'disabled-action': !isOrganizationVerified }"
                      :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''">
                    Delete Training
                  </li>
                  <li @click="updateTraining(training.trainingID)"
                      :class="{ 'disabled-action': !isOrganizationVerified }"
                      :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''">
                    Update Training
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Show More Button -->
        <button v-if="sortedOngoingTrainings.length > 4 && !isLoading" class="show-more-btn"
          @click="showAllOngoing = !showAllOngoing">
          {{ showAllOngoing ? 'Show Less' : 'Show More' }}
        </button>
      </section>

    <!-- ✅ Conducted Trainings Section -->
    <section class="completed">
      <div class="flex items-center justify-between">
        <h2 class="section-title flex items-center gap-1">
          Conducted Trainings
          <span class="count-badge">{{ sortedCompletedTrainings.length }}</span>
        </h2>
      </div>

      <!-- Loader -->
      <div v-if="isLoading" class="flex justify-center py-8">
        <svg
          class="animate-spin h-10 w-10 text-blue-600"
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

      <!-- Trainings Grid -->
      <div v-else class="trainings-grid">
        <div v-for="training in visibleFilteredCompleted" :key="training.trainingID" class="training-card"
          @click="openTrainingDetails(training)">
          <div class="training-right">
            <h3 class="training-title">{{ training.title }}</h3>
            <p class="training-date">{{ formatSchedule(training.schedule) }}</p>
          </div>

          <!-- Menu -->
          <div class="menu">
            <div class="menu-icon" @click.stop="toggleCompletedMenu(training.trainingID)">⋮</div>
            <div v-if="openCompletedMenu === training.trainingID" class="dropdown-menu" @click.stop>
              <ul>
                <li @click="deleteTraining(training.trainingID)"
                    :class="{ 'disabled-action': !isOrganizationVerified }"
                    :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''">
                  Delete Training
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Show More Button -->
      <button v-if="sortedCompletedTrainings.length > 4 && !isLoading" class="show-more-btn"
        @click="showAllCompleted = !showAllCompleted">
        {{ showAllCompleted ? 'Show Less' : 'Show More' }}
      </button>
    </section>

      <!-- All Trainings section removed as requested -->

      <!-- Training Details Modal -->
      <div v-if="showTrainingDetailsModal" class="modal-overlay" @click.self="closeTrainingDetails">
        <div class="training-details-modal">
          <button class="modal-close-btn" @click="closeTrainingDetails">✕</button>

          <div class="training-details-body">
            <!-- Training Name -->
            <div class="modal-title-row">
              <h3 class="modal-title">
                {{ selectedTraining.title }}
              </h3>
            </div>

            <!-- Tags -->
            <div
              v-if="selectedTraining?.Tags?.length"
              class="modal-tag-list"
            >
              <span
                v-for="tag in selectedTraining.Tags"
                :key="tag.TagID || tag.tagID || tag.id"
                class="modal-tag-chip"
              >
                {{ tag.TagName || tag.tagName || getTagName(tag.TagID || tag.tagID || tag.id) }}
              </span>
            </div>

            <!-- Description -->
            <p class="training-info">
              <strong>Description:</strong> {{ selectedTraining.description }}
            </p>

            <!-- Schedule/s Label -->
            <div class="training-info">
              <strong>Schedule/s:</strong>
            </div>

            <!-- Schedules Display (Card Style) -->
            <div class="schedules-container">
              <!-- Multiple Schedules -->
              <div 
                v-if="selectedTraining.schedules && selectedTraining.schedules.length > 0"
                class="schedules-grid"
              >
                <div 
                  v-for="(schedule, index) in selectedTraining.schedules" 
                  :key="schedule.trainingScheduleID || index"
                  class="schedule-card-wrapper"
                >
                  <div
                    class="schedule-card"
                    :class="{
                      'schedule-card-clickable': canGenerateQRForSchedule(schedule),
                      'schedule-card-active-qr': isScheduleQRActive(schedule),
                      'schedule-card-disabled': !canGenerateQRForSchedule(schedule)
                    }"
                    @click="handleScheduleClick(schedule)"
                  >

                  <!-- Date and Time -->
                  <div class="schedule-date-time">
                    <svg class="schedule-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ formatScheduleFull(schedule.schedule) }} - {{ formatScheduleTime(schedule.end_time) }}</span>
                  </div>

                  <!-- Mode Badge -->
                  <div class="schedule-mode-badge" :class="schedule.mode === 'On-Site' ? 'mode-onsite' : 'mode-online'">
                    <svg class="mode-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ schedule.mode }}</span>
                  </div>

                  <!-- Location (for On-Site) -->
                  <div v-if="schedule.mode === 'On-Site' && schedule.location" class="schedule-location">
                    <svg class="schedule-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ schedule.location }}</span>
                  </div>

                  <!-- Training Link (for Online) -->
                  <div v-if="schedule.mode === 'Online' && schedule.trainingLink" class="schedule-link">
                    <svg class="schedule-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <a :href="schedule.trainingLink" target="_blank" class="training-link" @click.stop>
                      {{ schedule.trainingLink }}
                    </a>
                  </div>
                  </div>

                  <!-- Hover Tooltip Below Card -->
                  <div class="schedule-hover-tooltip-below" v-if="getScheduleHoverText(schedule)">
                    {{ getScheduleHoverText(schedule) }}
                  </div>
                </div>
              </div>

              <!-- Single Schedule (Backward Compatibility) -->
              <div 
                v-else-if="selectedTraining.schedule" 
                class="schedule-card-wrapper"
              >
                <div
                  class="schedule-card"
                  :class="{
                    'schedule-card-clickable': canGenerateQRForSchedule(selectedTraining),
                    'schedule-card-disabled': !canGenerateQRForSchedule(selectedTraining)
                  }"
                  @click="handleScheduleClick(selectedTraining)"
                >

                <!-- Date and Time -->
                <div class="schedule-date-time">
                  <svg class="schedule-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ formatScheduleFull(selectedTraining.schedule) }} - {{ formatScheduleTime(selectedTraining.end_time) }}</span>
                </div>

                <!-- Mode Badge -->
                <div class="schedule-mode-badge" :class="selectedTraining.mode === 'On-Site' ? 'mode-onsite' : 'mode-online'">
                  <svg class="mode-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>{{ selectedTraining.mode }}</span>
                </div>

                <!-- Location (for On-Site) -->
                <div v-if="selectedTraining.mode === 'On-Site' && selectedTraining.location" class="schedule-location">
                  <svg class="schedule-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>{{ selectedTraining.location }}</span>
                </div>

                <!-- Training Link (for Online) -->
                <div v-if="selectedTraining.mode === 'Online' && selectedTraining.trainingLink" class="schedule-link">
                  <svg class="schedule-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                  </svg>
                  <a :href="selectedTraining.trainingLink" target="_blank" class="training-link" @click.stop>
                    {{ selectedTraining.trainingLink }}
                  </a>
                </div>
                </div>

                <!-- Hover Tooltip Below Card -->
                <div class="schedule-hover-tooltip-below" v-if="getScheduleHoverText(selectedTraining)">
                  {{ getScheduleHoverText(selectedTraining) }}
                </div>
              </div>

              <!-- No Schedule -->
              <div v-else class="schedule-card">
                <p class="text-gray-500">No schedule set</p>
              </div>
            </div>

          </div>

          <div class="registrants-section">
            <div class="registrants-header">
              <h4>Registrants ({{ filteredRegistrants.length }})</h4>
              <button class="registrants-refresh-btn" @click="loadRegistrantsForTraining()"
                :disabled="registrantsLoading">
                {{ registrantsLoading ? "Refreshing..." : "Refresh" }}
              </button>
            </div>

            <!-- Search Bar for Registrants -->
            <div class="registrants-search-wrapper" v-if="registrantsList.length > 0 && !registrantsLoading">
              <input 
                type="text" 
                v-model="registrantSearchQuery" 
                placeholder="Search by name, ID, or status..." 
                class="registrants-search-input"
              />
            </div>

            <p v-if="registrantsError" class="registrants-error">
              {{ registrantsError }}
            </p>
            <p v-else-if="registrantsLoading" class="registrants-loading">
              Loading registrants...
            </p>
            <p v-else-if="registrantsList.length === 0" class="registrants-empty">
              No registrants found for this training.
            </p>
            <p v-else-if="filteredRegistrants.length === 0" class="registrants-empty">
              No registrants match your search.
            </p>

            <div v-else-if="filteredRegistrants.length > 0" class="registrants-table-container inline">
              <table>
                <thead>
                  <tr>
                    <th>
                      <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
                    </th>
                    <th>Certificate Tracking ID</th>
                    <th>Full Name</th>
                    <th>Registration Date</th>
                    <th>Status</th>
                    <th class="cert-col-header">Certificate</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="person in filteredRegistrants" :key="person.id">
                    <td>
                      <input type="checkbox" v-model="person.selected" @change="updateSelectAll" />
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
                      <div class="button-tooltip-wrapper"
                        v-if="person.hasCertificate || person.status !== 'Attended'"
                        :title="person.hasCertificate ? 'Certificate already issued' : 'Registrant status must be Attended'">
                        <button class="action-btn"
                          :class="{
                            'certificate-issued-btn': person.hasCertificate,
                            'issue-cert-btn': !person.hasCertificate && person.status === 'Attended',
                            'disabled-action': person.status !== 'Attended' && !person.hasCertificate
                          }"
                          :disabled="person.hasCertificate || person.status !== 'Attended'"
                          @click.stop="handleIssueCertificate(person)">
                          {{ person.hasCertificate ? 'Certificate Issued' : 'Issue Certificate' }}
                        </button>
                      </div>
                      <button v-else
                        class="action-btn issue-cert-btn"
                        @click.stop="handleIssueCertificate(person)">
                        Issue Certificate
                      </button>
                      <button class="action-btn" v-if="person.hasCertificate && person.certificateUrl"
                        @click="viewCertificate(person.certificateUrl)">
                        View Certificate
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="registrants-footer" v-if="filteredRegistrants.length">
              <button class="bulk-issue-btn" @click="issueCertificatesToSelected"
                :disabled="!filteredRegistrants.some((p) => p.selected && !p.hasCertificate && p.status === 'Attended')">
                Issue Certificates to Selected
              </button>
            </div>
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
            <div class="input-with-counter">
              <input type="text" v-model="selectedRegistrant.certificateTrackingID" placeholder="Certificate Tracking ID"
                class="cert-input" required maxlength="50" />
              <span class="char-counter">{{ (selectedRegistrant.certificateTrackingID || '').length }}/50</span>
            </div>

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
              <input type="file" id="certificate-upload" accept="image/*,application/pdf" @change="handleFileUpload"
                class="hidden-file-input" />
              <label for="certificate-upload" class="cert-input upload-label">
                {{ selectedRegistrant.uploadedFile ? selectedRegistrant.uploadedFile.name : 'Upload File (PDF or Image)'
                }}
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
            <div class="input-with-counter">
              <input v-model="newTraining.title" type="text" placeholder="Title" class="training-input" maxlength="100" />
              <span class="char-counter">{{ (newTraining.title || '').length }}/100</span>
            </div>
            <div class="input-with-counter">
              <textarea v-model="newTraining.description" placeholder="Description" class="training-input" maxlength="1000"></textarea>
              <span class="char-counter">{{ (newTraining.description || '').length }}/1000</span>
            </div>


            <!-- Schedule - Multiple Dates -->
            <div class="popup-form-group schedule-group">
              <label for="schedule">Select Dates</label>
              <div class="schedule-input-wrapper">
                <!-- Date input with calendar icon for adding dates -->
                <div class="date-input-wrapper">
                  <input type="date" id="schedule" v-model="tempDateInput" :min="todayDate" placeholder="Select Date" />
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
                <button type="button" @click="addDate" class="add-date-btn" :disabled="!tempDateInput">Add Date</button>
              </div>

              <!-- List of selected dates with time and mode -->
              <div v-if="newTraining.schedules.length > 0" class="selected-dates-list">
                <div v-for="(schedule, index) in newTraining.schedules" :key="index" class="schedule-item">
                  <div class="schedule-item-header">
                    <span class="schedule-date">{{ formatDateDisplay(schedule.date) }}</span>
                    <button type="button" @click="removeSchedule(index)" class="remove-schedule-btn">×</button>
                  </div>

                  <div class="schedule-item-details">
                    <!-- Start Time and End Time in one row -->
                    <div class="time-inputs-row">
                      <!-- Start Time -->
                      <div class="time-input-wrapper">
                        <label :for="'startTime-' + index">Start Time</label>
                        <div class="date-input-wrapper">
                          <input type="time" :id="'startTime-' + index" v-model="schedule.startTime"
                            placeholder="Start Time" />
                          <span class="calendar-icon">
                            <svg width="26" height="26" viewBox="0 0 26 26" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                d="M13 2.16663C7.02012 2.16663 2.16669 7.02006 2.16669 13C2.16669 18.9799 7.02012 23.8333 13 23.8333C18.9799 23.8333 23.8334 18.9799 23.8334 13C23.8334 7.02006 18.9799 2.16663 13 2.16663ZM13 21.6666C8.10012 21.6666 4.33335 17.8999 4.33335 13C4.33335 8.10006 8.10012 4.33329 13 4.33329C17.9 4.33329 21.6667 8.10006 21.6667 13C21.6667 17.8999 17.9 21.6666 13 21.6666Z"
                                fill="black" />
                              <path
                                d="M13.8125 7.58337H12.1875V13.4067L16.9583 16.25L17.875 14.8334L13.8125 12.25V7.58337Z"
                                fill="black" />
                            </svg>
                          </span>
                        </div>
                      </div>

                      <!-- End Time -->
                      <div class="time-input-wrapper">
                        <label :for="'endTime-' + index">End Time</label>
                        <div class="date-input-wrapper">
                          <input type="time" :id="'endTime-' + index" v-model="schedule.endTime" placeholder="End Time" />
                          <span class="calendar-icon">
                            <svg width="26" height="26" viewBox="0 0 26 26" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                d="M13 2.16663C7.02012 2.16663 2.16669 7.02006 2.16669 13C2.16669 18.9799 7.02012 23.8333 13 23.8333C18.9799 23.8333 23.8334 18.9799 23.8334 13C23.8334 7.02006 18.9799 2.16663 13 2.16663ZM13 21.6666C8.10012 21.6666 4.33335 17.8999 4.33335 13C4.33335 8.10006 8.10012 4.33329 13 4.33329C17.9 4.33329 21.6667 8.10006 21.6667 13C21.6667 17.8999 17.9 21.6666 13 21.6666Z"
                                fill="black" />
                              <path
                                d="M13.8125 7.58337H12.1875V13.4067L16.9583 16.25L17.875 14.8334L13.8125 12.25V7.58337Z"
                                fill="black" />
                            </svg>
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Mode Selection -->
                    <div class="training-radio-group">
                      <label>
                        <input type="radio" :value="'On-Site'" v-model="schedule.mode" />
                        On-Site
                      </label>
                      <label>
                        <input type="radio" :value="'Online'" v-model="schedule.mode" />
                        Online
                      </label>
                    </div>

                    <!-- Conditional fields based on mode -->
                    <div v-if="schedule.mode === 'On-Site'" class="input-with-counter">
                      <input v-model="schedule.location" type="text"
                        placeholder="Location" class="training-input" maxlength="100" />
                      <span class="char-counter">{{ (schedule.location || '').length }}/100</span>
                    </div>

                    <div v-else-if="schedule.mode === 'Online'" class="input-with-counter">
                      <input v-model="schedule.trainingLink" type="url"
                        placeholder="Training Link" class="training-input" maxlength="100" />
                      <span class="char-counter">{{ (schedule.trainingLink || '').length }}/100</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tag selection -->
            <div class="relative mb-4">
              <label class="block font-semibold text-gray-600 mb-2">Tags</label>
              <!-- Search/Input for New Tag -->
              <div class="input-with-counter mb-2">
                <input v-model="newTagName" type="text" placeholder="Search or type a new tag..."
                  class="training-input" maxlength="50" />
                <span class="char-counter">{{ (newTagName || '').length }}/50</span>
              </div>
              <!-- Tag List Wrapper for horizontal scrolling -->
              <div class="tag-list-wrapper">
                <div class="tag-list">
                  <span v-for="tag in filteredTags" :key="tag.TagID" class="tag-chip" @click="toggleTag(tag.TagID)"
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
              <button @click.prevent="addTag" class="training-save-btn mt-2">Add Tag</button>
            </div>
            <!-- Save -->
            <button type="submit" class="training-post-btn" :disabled="!isOrganizationVerified" :title="!isOrganizationVerified ? 'Your organization account is not yet verified by the admin.' : ''">{{ isEditMode ? "Update" : "Post" }}</button>
          </form>
        </div>
      </div>

      <!-- QR Code Display Modal -->
      <div v-if="showQRModal && activeTrainingQR" class="qr-modal-overlay" @click.self="closeQRModal">
        <div class="qr-modal">
          <button class="qr-modal-close" @click="closeQRModal">✕</button>
          
          <div class="qr-modal-content">
            <h2 class="qr-modal-title">
              {{ selectedTraining.title || 'Training QR Code' }}
            </h2>
            
            <div v-if="activeScheduleForQR" class="qr-modal-schedule-info">
              <p class="qr-schedule-date">
                {{ formatScheduleFull(activeScheduleForQR.schedule) }} - 
                {{ formatScheduleTime(activeScheduleForQR.end_time) }}
              </p>
              <p class="qr-schedule-mode" :class="activeScheduleForQR.mode === 'On-Site' ? 'mode-onsite' : 'mode-online'">
                {{ activeScheduleForQR.mode }}
              </p>
            </div>
            
            <div class="qr-code-wrapper">
              <qrcode-vue :value="activeTrainingQR" :size="300" />
            </div>
            
            <div class="qr-modal-info">
              <p class="qr-expiry-info" v-if="qrExpiresAt">
                <strong>Expires at:</strong> {{ formatExpiryTime(qrExpiresAt) }}
              </p>
              <p class="qr-instructions">
                Scan this QR code with your device camera to check in for attendance.
                <br><br>
                <strong>Note:</strong> Only registered applicants can successfully scan and record attendance.
              </p>
            </div>
            
            <button class="qr-modal-download-btn" @click="downloadQRCode" v-if="activeTrainingQR">
              Download QR Code
            </button>
          </div>
        </div>
      </div>
      <div class="fixed top-5 right-5 space-y-2 z-50">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="[
            'px-4 py-2 rounded shadow flex items-center gap-2',
            toast.type === 'success'
              ? 'bg-white text-black'
              : toast.type === 'error'
              ? 'bg-red-500 text-white'
              : toast.type === 'confirm'
              ? 'bg-dark-slate text-white'
              : 'bg-gray-500 text-white',
          ]"
        >
          <span class="flex-1">{{ toast.message }}</span>

          <!-- ONLY SHOW WHEN CONFIRM -->
          <template v-if="toast.type === 'confirm'">
            <button
              @click="toast.onConfirm()"
              class="px-2 py-1 bg-white text-black rounded"
            >
              Yes
            </button>
            <button
              @click="toast.onCancel()"
              class="px-2 py-1 bg-gray-700 text-white rounded"
            >
              No
            </button>
          </template>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import axios from "axios";
import QrcodeVue from "qrcode.vue";
import api from "@/composables/api.js";
import { activeTrainingQR, activeTrainingId, activeScheduleId, qrExpiresAt, scheduleQR, generateQR } from "@/composables/useTrainingQR.js";
import { uploadCertificate, getPDFUrl, getImageUrl } from "@/lib/supabase.js";
import { useToast } from "@/composables/useToast.js";
import jsPDF from "jspdf";
import * as pdfjsLib from "pdfjs-dist";

const { toasts, showToast, showConfirmToast } = useToast();

// Configure pdfjs worker - use worker from public folder
// Files in public folder are served from root in both dev and production
pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdf.worker.min.mjs';

// Helper function to convert PDF blob/ArrayBuffer to PNG image
async function convertPDFToImage(pdfBlobOrArrayBuffer) {
  try {
    // Convert to ArrayBuffer if it's a Blob
    const data = pdfBlobOrArrayBuffer instanceof Blob
      ? await pdfBlobOrArrayBuffer.arrayBuffer()
      : pdfBlobOrArrayBuffer;

    const pdf = await pdfjsLib.getDocument({ data }).promise;
    const page = await pdf.getPage(1); // Get first page

    // Set scale for high quality (2x for better resolution)
    const scale = 2;
    const viewport = page.getViewport({ scale });

    // Create canvas
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    // Render PDF page to canvas
    await page.render({
      canvasContext: context,
      viewport: viewport
    }).promise;

    // Convert canvas to blob (PNG)
    return new Promise((resolve, reject) => {
      canvas.toBlob((blob) => {
        if (blob) {
          resolve(blob);
        } else {
          reject(new Error("Failed to convert canvas to blob"));
        }
      }, "image/png", 0.95); // 95% quality
    });
  } catch (error) {
    console.error("Error converting PDF to image:", error);
    throw error;
  }
}


export default {
  components: { QrcodeVue }, // ✅ register component
  data() {
    return {
      toasts: toasts,
      organizationLogo: null,
      organizationStatus: null,
      isOrganizationVerified: false,
      globalSearchQuery: '',
      showAllUpcoming: false,
      showAllOngoing: false,
      showAllCompleted: false,
      showAllTrainings: false,
      activeTrainingQR,  // <-- QR code value (reactive)
      activeTrainingId,  // <-- which training is active
      activeScheduleId,  // <-- which schedule is active
      qrExpiresAt,       // <-- QR expiry time
      isEditMode: false,
      trainingToEditId: null,
      qrCodeValue: null,
      qrExpiresAt: null,
      activeTrainingId: null, // which training shows the QR
      selectAll: false,
      isLoading: false, // ✅ loading state
      isOngoingLoading: false, // ✅ loading state for ongoing trainings
      
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
      openOngoingMenu: null,
      openCompletedMenu: null,
      openAllTrainingsMenu: null,

      showTrainingDetailsModal: false,
      selectedTraining: {},
      showQRModal: false,
      activeScheduleForQR: null, // Store which schedule has active QR

      registrantsList: [], // removed hardcoded list, fetch from DB
      registrantsLoading: false,
      registrantsError: "",
      registrantSearchQuery: "", // Search query for filtering registrants

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
      allTrainings: [], // All trainings from all organizations

      // Popup state + form
      showTrainingPopup: false,
      newTraining: {
        title: "",
        description: "",
        schedules: [], // Array of { date, startTime, endTime, mode, location, trainingLink }
        Tags: []
      },
      tempDateInput: "", // Temporary date input for adding new dates

      QrcodeVue: "",
      qrExpiresAt: "",
      activeTrainingId: null, // which training shows the QR
      tagOptions: [],
      newTagName: '',
    };
  },

  methods: {

    viewCertificate(certificatePath) {
      if (!certificatePath) {
        showToast("Certificate not found.", "error");
        return;
      }

      // Get public URL from Supabase
      const publicUrl = getPDFUrl(certificatePath);
      if (!publicUrl) {
        showToast("Unable to generate certificate URL.", "error");
        return;
      }

      window.open(publicUrl, "_blank"); // Open in new tab
    },

    handleIssueCertificate(person) {
      // Early return if disabled conditions are met
      if (person.hasCertificate) return;
      if (person.status !== 'Attended') {
        showToast('Certificates can only be issued to registrants with "Attended" status.', "error");
        return;
      }
      // Proceed with issuing certificate
      this.issueCertificate(person);
    },

    async issueCertificate(person) {
      try {
        if (person.hasCertificate) return;
        if (person.status !== 'Attended') {
          showToast('Certificates can only be issued to registrants with "Attended" status.', "error");
          return;
        }

        // Generate PDF Certificate
        const givenDate = new Date().toISOString().split("T")[0];

        const doc = new jsPDF({ orientation: "landscape", unit: "pt", format: "a4" });
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();

        const lines = [
          { text: "Certificate of Completion", size: 28 },
          { text: `This is to certify that ${person.name}`, size: 22 },
          { text: `has completed the training: ${this.selectedTraining.title}`, size: 18 },
          { text: `Certificate Tracking ID: ${person.id}`, size: 14 },
          { text: `Date Issued: ${givenDate}`, size: 14 }
        ];

        let startY = (pageHeight - lines.reduce((sum, line) => sum + line.size + 10, 0)) / 2;
        lines.forEach(line => {
          doc.setFontSize(line.size);
          doc.text(line.text, pageWidth / 2, startY, null, null, "center");
          startY += line.size + 10;
        });

        const pdfBlob = doc.output("blob");
        const safeName = person.name.replace(/[/\\?%*:|"<>]/g, "_");

        // Convert PDF to image on frontend (since Imagick is not available on server)
        const imageBlob = await convertPDFToImage(pdfBlob);
        const imageFile = new File([imageBlob], `${safeName}.png`, { type: "image/png" });

        // Send image file to backend - backend will upload to Supabase
        const token = localStorage.getItem("token");
        if (!token) {
          showToast("Please log in to continue.", "error");
          return;
        }

        const formData = new FormData();
        formData.append("certificateTrackingID", String(person.id)); // Ensure it's a string
        formData.append("certificateGivenDate", givenDate);
        formData.append("file", imageFile); // Send image (PDF already converted)

        // Send to backend (backend handles conversion and Supabase upload)
        const response = await axios.post(
          `${import.meta.env.VITE_API_BASE_URL}/registrations/${person.id}/certificate`,
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data",
            },
          }
        );

        // Update UI
        person.hasCertificate = true;
        person.certificateTrackingID = person.id;
        person.certificatePath = response.data.data?.certificatePath || null;

        // Generate public URL for viewing
        if (person.certificatePath) {
          const { getPDFUrl } = await import("@/lib/supabase");
          person.certificateUrl = getPDFUrl(person.certificatePath);
        }

        showToast(`Certificate issued for ${person.name}!`, "success");

      } catch (error) {
        console.error("Error issuing certificate:", error);
        showToast(`Failed to issue certificate for ${person.name}.`, "error");
      }
    },
    async issueBulkCertificates(selectedRegistrants) {
      try {
        const givenDate = new Date().toISOString().split("T")[0];
        const token = localStorage.getItem("token");
        if (!token) {
          showToast("Please log in to continue.", "error");
          return;
        }

        // Issue certificates one by one (backend converts PDF to image)
        for (const person of selectedRegistrants) {
          const doc = new jsPDF({ orientation: "landscape", unit: "pt", format: "a4" });
          const pageWidth = doc.internal.pageSize.getWidth();
          const pageHeight = doc.internal.pageSize.getHeight();

          const lines = [
            { text: "Certificate of Completion", size: 28 },
            { text: `This is to certify that ${person.name}`, size: 22 },
            { text: `has completed the training: ${this.selectedTraining.title}`, size: 18 },
            { text: `Certificate Tracking ID: ${person.id}`, size: 14 },
            { text: `Date Issued: ${givenDate}`, size: 14 }
          ];

          let startY = (pageHeight - lines.reduce((sum, line) => sum + line.size + 10, 0)) / 2;
          lines.forEach(line => {
            doc.setFontSize(line.size);
            doc.text(line.text, pageWidth / 2, startY, null, null, "center");
            startY += line.size + 10;
          });

          const pdfBlob = doc.output("blob");

          // Convert PDF to image on frontend (since Imagick is not available on server)
          const imageBlob = await convertPDFToImage(pdfBlob);
          const imageFile = new File([imageBlob], `${person.name}.png`, { type: "image/png" });

          // Send image file to backend - backend uploads to Supabase
          const formData = new FormData();
          formData.append("certificateTrackingID", String(person.id)); // Ensure it's a string
          formData.append("certificateGivenDate", givenDate);
          formData.append("file", imageFile); // Send image (PDF already converted)

          await axios.post(
            `${import.meta.env.VITE_API_BASE_URL}/registrations/${person.id}/certificate`,
            formData,
            { headers: { Authorization: `Bearer ${token}`, "Content-Type": "multipart/form-data" } }
          );
        }

        showToast("Bulk certificates issued successfully!", "success");
      } catch (error) {
        console.error("Error issuing bulk certificates:", error);
        showToast("Failed to issue bulk certificates. " + (error.response?.data?.message || error.message), "error");
      }
    },

    async issueCertificatesToSelected() {
      const selectedPeople = this.registrantsList.filter(p => p.selected && !p.hasCertificate && p.status === 'Attended');
      if (!selectedPeople.length) {
        showToast("No selected registrants with 'Attended' status or all already issued.", "error");
        return;
      }

      try {
        const certGivenDate = new Date().toISOString().split("T")[0];
        const token = localStorage.getItem("token");
        if (!token) {
          showToast("Please log in to continue.", "error");
          return;
        }

        // Issue certificates one by one (backend converts PDF to image)
        for (const person of selectedPeople) {
          const doc = new jsPDF({ orientation: "landscape", unit: "pt", format: "a4" });
          const pageWidth = doc.internal.pageSize.getWidth();
          const pageHeight = doc.internal.pageSize.getHeight();

          const lines = [
            { text: "Certificate of Completion", size: 28 },
            { text: `This is to certify that ${person.name}`, size: 22 },
            { text: `has completed the training: ${this.selectedTraining.title}`, size: 18 },
            { text: `Certificate Tracking ID: ${person.id}`, size: 14 },
            { text: `Date Issued: ${certGivenDate}`, size: 14 }
          ];

          let startY = (pageHeight - lines.reduce((sum, line) => sum + line.size + 10, 0)) / 2;
          lines.forEach(line => {
            doc.setFontSize(line.size);
            doc.text(line.text, pageWidth / 2, startY, null, null, "center");
            startY += line.size + 10;
          });

          const pdfBlob = doc.output("blob");

          // Convert PDF to image on frontend (since Imagick is not available on server)
          const imageBlob = await convertPDFToImage(pdfBlob);
          const imageFile = new File([imageBlob], `${person.id}.png`, { type: "image/png" });

          // Send image file to backend - backend uploads to Supabase
          const formData = new FormData();
          formData.append("certificateTrackingID", String(person.id)); // Ensure it's a string
          formData.append("certificateGivenDate", certGivenDate);
          formData.append("file", imageFile); // Send image (PDF already converted)

          await axios.post(
            `${import.meta.env.VITE_API_BASE_URL}/registrations/${person.id}/certificate`,
            formData,
            { headers: { Authorization: `Bearer ${token}`, "Content-Type": "multipart/form-data" } }
          );
        }

        // Update UI
        selectedPeople.forEach(p => {
          p.hasCertificate = true;
          p.certificateTrackingID = p.id;
        });

        showToast("Certificates issued successfully!", "success");

        // Refresh registrants list
        if (this.selectedTraining) {
          await this.loadRegistrantsForTraining(this.selectedTraining);
        }
      } catch (error) {
        console.error("Bulk issuance error:", error);
        showToast("Failed to issue certificates. " + (error.response?.data?.message || error.message), "error");
      }
    },

    toggleSelectAll() {
      // Only toggle selection for filtered/visible registrants
      this.filteredRegistrants.forEach(person => {
        person.selected = this.selectAll;
      });
    },

    updateSelectAll() {
      // Check if all filtered registrants are selected
      if (this.filteredRegistrants.length === 0) {
        this.selectAll = false;
        return;
      }
      this.selectAll = this.filteredRegistrants.every(person => person.selected);
    },
    openBulkCertModal() {
      const registrantsWithoutCert = this.registrantsList.filter(r => !r.hasCertificate);
      if (registrantsWithoutCert.length === 0) {
        showToast("All registrants already have certificates.", "error");
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
        showToast("Please log in to continue.", "error");
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

        showToast("Certificates issued successfully to all registrants!", "success");
        this.closeBulkCertModal();
        await this.loadRegistrantsForTraining(this.selectedTraining); // Refresh list
      } catch (error) {
        console.error("Error issuing bulk certificates:", error);
        if (error.response) {
          showToast(error.response.data.message || "Failed to issue certificates.", "error");
        } else {
          showToast(error.message || "An error occurred while issuing certificates.", "error");
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
      const trimmedTagName = this.newTagName.trim();
      if (!trimmedTagName) {
        showToast("Please enter a tag name.", "error");
        return;
      }

      // Check if tag already exists (case-insensitive)
      const existingTag = this.tagOptions.find(
        tag => tag.TagName.toLowerCase() === trimmedTagName.toLowerCase()
      );

      if (existingTag) {
        // Tag exists, just select it if not already selected
        const normalizedId = Number(existingTag.TagID);
        if (!this.newTraining.Tags.includes(normalizedId)) {
          this.newTraining.Tags.push(normalizedId);
        }
        // Clear the input field
        this.newTagName = "";
        return;
      }

      // Tag doesn't exist, create it
      try {
        // Send the new tag to the backend
        const response = await axios.post(import.meta.env.VITE_API_BASE_URL + '/tags', {
          TagName: trimmedTagName
        });

        // Add the new tag to the tagOptions and newTraining.Tags
        this.tagOptions.push(response.data);
        this.newTraining.Tags.push(Number(response.data.TagID));

        // Clear the input field
        this.newTagName = '';
      } catch (error) {
        console.error("Error adding tag:", error);
        showToast('Failed to create tag. Please try again.', "error");
      }
    },

    // Schedule management methods
    addDate() {
      if (!this.tempDateInput) {
        showToast("Please select a date first.", "error");
        return;
      }

      // Check if date already exists
      const dateExists = this.newTraining.schedules.some(s => s.date === this.tempDateInput);
      if (dateExists) {
        showToast("This date has already been added.", "error");
        return;
      }

      // Add new schedule with default values
      this.newTraining.schedules.push({
        date: this.tempDateInput,
        startTime: "",
        endTime: "",
        mode: "",
        location: "",
        trainingLink: ""
      });

      // Clear the temporary input
      this.tempDateInput = "";
    },

    removeSchedule(index) {
      this.newTraining.schedules.splice(index, 1);
    },

    formatDateDisplay(dateString) {
      if (!dateString) return "";
      const date = new Date(dateString + "T00:00:00");
      return date.toLocaleDateString("en-US", {
        weekday: "short",
        year: "numeric",
        month: "short",
        day: "numeric"
      });
    },

    async sendCertificateDetails() {
      if (!this.selectedRegistrant || !this.selectedRegistrant.id) {
        console.error("No registrant ID found!");
        return;
      }

      if (!this.selectedRegistrant.uploadedFile) {
        showToast("Please select a certificate file before sending.", "error");
        return;
      }

      const token = localStorage.getItem("token");
      if (!token) {
        showToast("Please log in to continue.", "error");
        return;
      }

      // Convert PDF to image if uploaded file is a PDF
      let fileToUpload = this.selectedRegistrant.uploadedFile;
      if (fileToUpload.type === "application/pdf" || fileToUpload.name.toLowerCase().endsWith(".pdf")) {
        try {
          const pdfBlob = await fileToUpload.arrayBuffer();
          const imageBlob = await convertPDFToImage(pdfBlob);
          fileToUpload = new File([imageBlob], fileToUpload.name.replace(/\.pdf$/i, ".png"), { type: "image/png" });
        } catch (error) {
          console.error("Error converting PDF to image:", error);
          showToast("Failed to convert PDF to image. Please try uploading an image file instead.", "error");
          return;
        }
      }

      const formData = new FormData();
      formData.append("certificateTrackingID", String(this.selectedRegistrant.certificateTrackingID || this.selectedRegistrant.id));
      formData.append("certificateGivenDate", this.selectedRegistrant.certificateGivenDate);
      formData.append("file", fileToUpload); // Send image (PDF already converted if it was a PDF)

      try {
        const res = await axios.post(
          `${import.meta.env.VITE_API_BASE_URL}/registrations/${this.selectedRegistrant.id}/certificate`,
          formData,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "multipart/form-data"
            }
          }
        );
        console.log("Certificate uploaded successfully", res.data);
        showToast("Certificate uploaded successfully!", "success");
        this.showCertUploadModal = false;

        // Refresh registrants list if details modal is open
        if (this.showTrainingDetailsModal && this.selectedTraining) {
          await this.loadRegistrantsForTraining(this.selectedTraining);
        }
      } catch (err) {
        console.error("Error uploading certificate: ", err);
        showToast(err.response?.data?.message || "Failed to upload certificate. Please try again.", "error");
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

    /* ==========================
       ✅ Dropdown Menu Logic
    ========================== */
    toggleUpcomingMenu(id) {
      this.openUpcomingMenu = this.openUpcomingMenu === id ? null : id;
      this.openOngoingMenu = null;
      this.openCompletedMenu = null;
    },
    toggleOngoingMenu(id) {
      this.openOngoingMenu = this.openOngoingMenu === id ? null : id;
      this.openUpcomingMenu = null;
      this.openCompletedMenu = null;
    },
    toggleCompletedMenu(id) {
      this.openCompletedMenu = this.openCompletedMenu === id ? null : id;
      this.openUpcomingMenu = null;
      this.openOngoingMenu = null;
    },

    closeAllMenus() {
      this.openUpcomingMenu = null;
      this.openOngoingMenu = null;
      this.openCompletedMenu = null;
      this.openAllTrainingsMenu = null;
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
    async issueCertificate(person) {
      try {
        if (person.hasCertificate) return;

        const givenDate = new Date().toISOString().split("T")[0];

        // Generate PDF
        const doc = new jsPDF({ orientation: "landscape", unit: "pt", format: "a4" });
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();

        const lines = [
          { text: "Certificate of Completion", size: 28 },
          { text: `This is to certify that ${person.name}`, size: 22 },
          { text: `has completed the training: ${this.selectedTraining.title}`, size: 18 },
          { text: `Certificate Tracking ID: ${person.id}`, size: 14 },
          { text: `Date Issued: ${givenDate}`, size: 14 }
        ];

        let startY = (pageHeight - lines.reduce((sum, l) => sum + l.size + 10, 0)) / 2;

        lines.forEach(line => {
          doc.setFontSize(line.size);
          doc.text(line.text, pageWidth / 2, startY, null, null, "center");
          startY += line.size + 10;
        });

        const pdfBlob = doc.output("blob");
        const safeName = person.name.replace(/[/\\?%*:|"<>]/g, "_");

        // Convert PDF to image on frontend (since Imagick is not available on server)
        const imageBlob = await convertPDFToImage(pdfBlob);
        const imageFile = new File([imageBlob], `${safeName}.png`, { type: "image/png" });

        // Send image file to backend - backend will upload to Supabase
        const token = localStorage.getItem("token");
        if (!token) {
          showToast("Please log in to continue.", "error");
          return;
        }

        const formData = new FormData();
        formData.append("certificateTrackingID", String(person.id)); // Ensure it's a string
        formData.append("certificateGivenDate", givenDate);
        formData.append("file", imageFile); // Send image (PDF already converted)

        // Send to backend (backend handles conversion and Supabase upload)
        const response = await axios.post(
          `${import.meta.env.VITE_API_BASE_URL}/registrations/${person.id}/certificate`,
          formData,
          { headers: { Authorization: `Bearer ${token}`, "Content-Type": "multipart/form-data" } }
        );

        // Update UI
        person.hasCertificate = true;
        person.certificateTrackingID = person.id;
        person.certificatePath = response.data.data?.certificatePath || null;

        // Generate public URL for viewing
        if (person.certificatePath) {
          const { getPDFUrl } = await import("@/lib/supabase");
          person.certificateUrl = getPDFUrl(person.certificatePath);
        }

        showToast(`Certificate issued for ${person.name}!`, "success");
      } catch (error) {
        console.error("Error issuing certificate:", error);
        showToast(`Failed to issue certificate for ${person.name}.`, "error");
      }
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
        showToast("Please select a certificate file before sending.", "error");
        return;
      }

      this.sendCertificate(
        this.selectedRegistrant.uploadedFile,
        this.selectedRegistrant.id
      ).then(() => {
        this.closeCertUploadModal();
      }).catch(err => {
        console.error(err);
        showToast("Failed to send certificate.", "error");
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
      this.selectedTraining = {
        ...training
      };
      this.showTrainingDetailsModal = true;
      this.closeAllMenus();
      this.selectAll = false;
      this.registrantsError = "";
      this.registrantsList = [];
      this.registrantSearchQuery = ""; // Clear search when opening modal
      this.loadRegistrantsForTraining(training);
    },

    closeTrainingDetails() {
      this.showTrainingDetailsModal = false;
      this.registrantsList = [];
      this.registrantsError = "";
      this.registrantsLoading = false;
      this.registrantSearchQuery = ""; // Clear search when closing modal
    },

    async loadRegistrantsForTraining(training = null) {
      const targetTraining = training || this.selectedTraining;
      if (!targetTraining?.trainingID) {
        console.warn("No training provided for registrants list.");
        return;
      }

      this.selectedTraining = targetTraining;

      const token = localStorage.getItem("token");
      if (!token) {
        showToast("Please log in to continue.", "error");
        return;
      }

      this.registrantsLoading = true;
      this.registrantsError = "";

      try {
        const response = await axios.get(
          `${import.meta.env.VITE_API_BASE_URL}/trainings/${targetTraining.trainingID}/registrants`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        this.registrantsList = response.data.map((r) => ({
          ...r,
          selected: false,
          certificateUrl: r.certificatePath ? getPDFUrl(r.certificatePath) : null,
          hasCertificate:
            r.hasCertificate !== undefined
              ? r.hasCertificate
              : Boolean(r.certificatePath),
        }));
        this.selectAll = false;
        this.closeAllMenus();
      } catch (error) {
        console.error("Error fetching registrants:", error);
        this.registrantsError =
          error.response?.data?.message ||
          "Failed to fetch registrants. Please try again.";
      } finally {
        this.registrantsLoading = false;
      }
    },

async fetchTrainings() {
    this.isLoading = true; // start loading
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

      const normalizedList = Array.isArray(newTrainings) ? newTrainings : [];

      // Rebuild local list from fresh API data
      this.upcomingtrainings = [];
      normalizedList.forEach(training => {
        const normalizedTraining = { ...training };
        this.upcomingtrainings.push(normalizedTraining);

        // ✅ Schedule QR using composable
        scheduleQR(training);
      });
    } catch (error) {
      console.error("Error fetching trainings:", error);
    } finally {
      this.isLoading = false; // stop loading
    }
  },

    async fetchAllTrainings() {
      try {
        // Fetch all trainings without organizationID filter
        const { data } = await api.get("/trainings");
        
        this.allTrainings = data.map(training => ({
          ...training,
          isOrganizationChoice: Boolean(training.isOrganizationChoice)
        }));

        // Schedule QR for all trainings
        this.allTrainings.forEach(training => {
          scheduleQR(training);
        });
      } catch (error) {
        console.error("Error fetching all trainings:", error);
      }
    },

    isTrainingOwnedByCurrentOrg(training) {
      const orgId = this.currentOrganizationId;
      if (!orgId) return false;
      const trainingOrgId = training.organizationID || training.organization_id;
      return trainingOrgId === orgId;
    },

    toggleAllTrainingsMenu(id) {
      this.openAllTrainingsMenu = this.openAllTrainingsMenu === id ? null : id;
      this.openUpcomingMenu = null;
      this.openOngoingMenu = null;
      this.openCompletedMenu = null;
    },

    openTrainingPopup() {
      // Check if organization is verified
      if (!this.isOrganizationVerified) {
        showToast("Your organization account is not yet verified by the admin. Please wait for admin approval before creating or editing trainings.", "error");
        return;
      }
      this.showTrainingPopup = true;
      this.fetchTags(); // Load tags into dropdown
    },

    async openTrainingPopup(training = null) {
      await this.fetchTags(); // Load tags into dropdown

      if (training) {
        this.isEditMode = true;
        this.trainingToEditId = training.trainingID;
        
        // Convert schedules from API format to form format
        let schedules = [];
        if (training.schedules && Array.isArray(training.schedules) && training.schedules.length > 0) {
          // Multiple schedules from API
          schedules = training.schedules.map(schedule => {
            const scheduleDate = schedule.schedule || schedule.Schedule || "";
            const endTime = schedule.end_time || schedule.endTime || "";
            
            const scheduleParts = scheduleDate.includes("T")
              ? scheduleDate.split("T")
              : scheduleDate.split(" ") || [];
            const endTimeParts = endTime.includes("T")
              ? endTime.split("T")
              : endTime.split(" ") || [];
            
            return {
              date: scheduleParts[0] || "",
              startTime: scheduleParts[1]?.slice(0, 5) || "",
              endTime: endTimeParts[1]?.slice(0, 5) || "",
              mode: schedule.mode || schedule.Mode || "",
              location: schedule.location || schedule.Location || "",
              trainingLink: schedule.trainingLink || schedule.training_link || schedule.TrainingLink || ""
            };
          });
        } else if (training.schedule) {
          // Single schedule (backward compatibility)
          const scheduleParts = training.schedule.includes("T")
            ? training.schedule.split("T")
            : training.schedule.split(" ") || [];
          const endTimeParts = training.end_time
            ? (training.end_time.includes("T") ? training.end_time.split("T") : training.end_time.split(" "))
            : [];
          
          schedules = [{
            date: scheduleParts[0] || "",
            startTime: scheduleParts[1]?.slice(0, 5) || "",
            endTime: endTimeParts[1]?.slice(0, 5) || "",
            mode: training.mode || "",
            location: training.location || "",
            trainingLink: training.trainingLink || training.training_link || ""
          }];
        }
        
        this.newTraining = {
          title: training.title || training.Title || "",
          description: training.description || training.Description || "",
          schedules: schedules,
          Tags: training.Tags
            ? training.Tags.map(tag => Number(tag.TagID ?? tag.tagID ?? tag.id))
            : []
        };
      } else {
        this.isEditMode = false;
        this.newTraining = {
          title: "",
          description: "",
          schedules: [],
          Tags: []
        };
        this.tempDateInput = "";
      }

      if (!Array.isArray(this.newTraining.Tags)) {
        this.newTraining.Tags = [];
      }
      if (!Array.isArray(this.newTraining.schedules)) {
        this.newTraining.schedules = [];
      }

      this.showTrainingPopup = true;
    },

    closeTrainingPopup() {
      this.showTrainingPopup = false;
      this.newTraining = {
        title: "",
        description: "",
        schedules: [],
        Tags: []
      };
      this.tempDateInput = "";
      this.newTagName = ""; // optional: clear the tag input too
    },

    async updateTraining(trainingID) {
      const training = this.upcomingtrainings.find(t => t.trainingID === trainingID) 
        || this.completedtrainings.find(t => t.trainingID === trainingID);
      
      if (!training) {
        showToast("Training not found.", "error");
        return;
      }

      // Use the same logic as openTrainingPopup to handle both single and multiple schedules
      await this.openTrainingPopup(training);
      this.closeAllMenus();
    },

    // For saving (create/update) training
    async saveTraining() {
      // Check if organization is verified
      if (!this.isOrganizationVerified) {
        showToast("Your organization account is not yet verified by the admin. Please wait for admin approval before performing this action.", "error");
        return;
      }

      try {
        // Validate schedules
        if (!this.newTraining.schedules || this.newTraining.schedules.length === 0) {
          showToast("PLEASE ADD AT LEAST ONE DATE WITH TIME AND MODE", "error");
          return;
        }

        // Validate each schedule
        for (let i = 0; i < this.newTraining.schedules.length; i++) {
          const schedule = this.newTraining.schedules[i];
          if (!schedule.date || !schedule.startTime || !schedule.endTime || !schedule.mode) {
            showToast(`PLEASE COMPLETE ALL FIELDS FOR DATE: ${this.formatDateDisplay(schedule.date) || 'Date ' + (i + 1)}`, "error");
            return;
          }
          if (schedule.mode === "On-Site" && !schedule.location) {
            showToast(`PLEASE ENTER A LOCATION FOR DATE: ${this.formatDateDisplay(schedule.date)}`, "error");
            return;
          }
          if (schedule.mode === "Online" && !schedule.trainingLink) {
            showToast(`PLEASE ENTER A TRAINING LINK FOR DATE: ${this.formatDateDisplay(schedule.date)}`, "error");
            return;
          }
        }

        const token = localStorage.getItem("token");

        if (this.isEditMode && this.trainingToEditId) {
          // For edit mode, update the training with all schedules
          const schedules = this.newTraining.schedules.map(schedule => {
            const combinedSchedule = `${schedule.date} ${schedule.startTime}`;
            const endTimeSchedule = `${schedule.date} ${schedule.endTime}`;

            return {
              schedule: combinedSchedule,
              end_time: endTimeSchedule,
              mode: schedule.mode,
              location: schedule.mode === "On-Site" ? schedule.location || null : null,
              training_link: schedule.mode === "Online" ? schedule.trainingLink || null : null,
            };
          });

          const payload = {
            title: this.newTraining.title,
            description: this.newTraining.description,
            schedules: schedules, // Array of schedule objects
            Tags: this.newTraining.Tags || []
          };

          await axios.put(
            `${import.meta.env.VITE_API_BASE_URL}/trainings/${this.trainingToEditId}`,
            payload,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          showToast("✅ TRAINING UPDATED SUCCESSFULLY!", "success");
        } else {
          // For create mode, create ONE training with MULTIPLE schedules
          // Format schedules array according to database schema
          const schedules = this.newTraining.schedules.map(schedule => {
            const combinedSchedule = `${schedule.date} ${schedule.startTime}`;
            const endTimeSchedule = `${schedule.date} ${schedule.endTime}`;

            return {
              schedule: combinedSchedule,
              end_time: endTimeSchedule,
              mode: schedule.mode,
              location: schedule.mode === "On-Site" ? schedule.location || null : null,
              training_link: schedule.mode === "Online" ? schedule.trainingLink || null : null,
            };
          });

          const payload = {
            title: this.newTraining.title,
            description: this.newTraining.description,
            schedules: schedules, // Array of schedule objects
            Tags: this.newTraining.Tags || []
          };

          const response = await axios.post(
            `${import.meta.env.VITE_API_BASE_URL}/trainings`,
            payload,
            { headers: { Authorization: `Bearer ${token}` } }
          );

          showToast(`✅ TRAINING WITH ${this.newTraining.schedules.length} SCHEDULE(S) POSTED SUCCESSFULLY!`, "success");
        }

        await this.fetchTrainings();
        this.closeTrainingPopup();
        this.isEditMode = false;
        this.trainingToEditId = null;

      } catch (error) {
        console.error("ERROR SAVING TRAINING:", error.response?.data || error);
        showToast("❌ SOMETHING WENT WRONG WHILE SAVING THE TRAINING", "error");
      }
    },

    // For deleting training
    async deleteTraining(trainingID) {
      // Check if organization is verified
      if (!this.isOrganizationVerified) {
        showToast("Your organization account is not yet verified by the admin. Please wait for admin approval before performing this action.", "error");
        return;
      }

      try {
        if (!confirm("Are you sure you want to delete this training?")) return;

        const token = localStorage.getItem("token");

        await axios.delete(
          `${import.meta.env.VITE_API_BASE_URL}/trainings/${trainingID}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        showToast("✅ Training deleted successfully!", "success");
        await this.fetchTrainings();

        if (this.activeTrainingId === trainingID) {
          this.qrCodeValue = null;
          this.qrExpiresAt = null;
          this.activeTrainingId = null;
          if (this.qrExpireTimeout) clearTimeout(this.qrExpireTimeout);
        }

      } catch (error) {
        console.error("Failed to delete training:", error.response?.data || error);
        showToast("❌ Something went wrong while deleting the training.", "error");
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
    formatScheduleFull(schedule) {
      if (!schedule) return "No schedule set";
      try {
        const [datePart, timePart] = schedule.split(/[ T]/);
        const [year, month, day] = datePart.split("-");
        const [hour, minute] = (timePart || "00:00:00").split(":");

        const date = new Date(
          Number(year),
          Number(month) - 1,
          Number(day),
          Number(hour),
          Number(minute)
        );

        return date.toLocaleString("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
          hour12: true,
        });
      } catch (error) {
        return schedule || "Invalid date";
      }
    },
    formatScheduleTime(schedule) {
      if (!schedule) return "";
      try {
        const [datePart, timePart] = schedule.split(/[ T]/);
        const [year, month, day] = datePart.split("-");
        const [hour, minute] = (timePart || "00:00:00").split(":");

        const date = new Date(
          Number(year),
          Number(month) - 1,
          Number(day),
          Number(hour),
          Number(minute)
        );

        return date.toLocaleString("en-US", {
          hour: "2-digit",
          minute: "2-digit",
          hour12: true,
        });
      } catch (error) {
        return "";
      }
    },

    /**
     * Format expiry time for QR code display
     * Handles ISO 8601 format with timezone (e.g., "2025-11-29T12:00:00+08:00")
     */
    formatExpiryTime(expiresAt) {
      if (!expiresAt) return "";
      try {
        // Parse the date - ISO 8601 format should be parsed correctly by JavaScript
        const date = new Date(expiresAt);
        
        // Format in Asia/Manila timezone
        return date.toLocaleString('en-US', {
          timeZone: 'Asia/Manila',
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          hour12: true,
          timeZoneName: 'short'
        });
      } catch (error) {
        console.error("Error formatting expiry time:", error, expiresAt);
        // Fallback: try to display as-is
        return expiresAt.toString();
      }
    },

    /* ✅ Schedule QR Code Methods */
    
    /**
     * Parse date string to local Date object (prevents timezone issues)
     * Supports formats: "2025-12-01 07:00:00" or "2025-12-01T07:00:00"
     */
    parseLocalDateTime(dateString) {
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
    },

    /**
     * Check if a schedule is currently active (within its timeframe)
     */
    isScheduleActive(schedule) {
      if (!schedule) {
        return false;
      }
      
      // Support both multiple schedules format and single schedule format
      const scheduleTime = schedule.schedule || schedule.Schedule;
      const endTime = schedule.end_time || schedule.endTime;
      
      if (!scheduleTime || !endTime) {
        return false;
      }
      
      const now = new Date();
      const startTime = this.parseLocalDateTime(scheduleTime);
      const endTimeDate = this.parseLocalDateTime(endTime);
      
      if (!startTime || !endTimeDate) {
        return false;
      }
      
      return now >= startTime && now < endTimeDate;
    },

    /**
     * Check if schedule has started
     */
    hasScheduleStarted(schedule) {
      if (!schedule) {
        return false;
      }
      const scheduleTime = schedule.schedule || schedule.Schedule;
      if (!scheduleTime) {
        return false;
      }
      const now = new Date();
      const startTime = this.parseLocalDateTime(scheduleTime);
      if (!startTime) {
        return false;
      }
      return now >= startTime;
    },

    /**
     * Check if schedule has ended
     */
    hasScheduleEnded(schedule) {
      if (!schedule) {
        return false;
      }
      const endTime = schedule.end_time || schedule.endTime;
      if (!endTime) {
        return false;
      }
      const now = new Date();
      const endTimeDate = this.parseLocalDateTime(endTime);
      if (!endTimeDate) {
        return false;
      }
      return now >= endTimeDate;
    },

    /**
     * Get hover tooltip text for schedule card
     */
    getScheduleHoverText(schedule) {
      if (!schedule) return "";
      
      if (!this.hasScheduleStarted(schedule)) {
        return "Generate QR not available. Training not yet started";
      }
      
      if (this.hasScheduleEnded(schedule)) {
        return "Generate QR not available. Training already ended";
      }
      
      if (this.isScheduleActive(schedule)) {
        return "Generate QR?";
      }
      
      return "";
    },

    /**
     * Check if schedule can generate QR
     */
    canGenerateQRForSchedule(schedule) {
      return this.isScheduleActive(schedule);
    },

    /**
     * Handle schedule card click to generate QR
     */
    async handleScheduleClick(schedule) {
      console.log("Schedule clicked:", schedule);
      console.log("Schedule start:", schedule.schedule || schedule.Schedule);
      console.log("Schedule end:", schedule.end_time || schedule.endTime);
      console.log("Current time:", new Date());
      
      if (!this.canGenerateQRForSchedule(schedule)) {
        if (!this.hasScheduleStarted(schedule)) {
          const startTime = this.parseLocalDateTime(schedule.schedule || schedule.Schedule);
          showToast(`Cannot generate QR code. Training has not started yet.\nStart time: ${startTime ? startTime.toLocaleString() : 'Unknown'}\nCurrent time: ${new Date().toLocaleString()}`, "error");
        } else if (this.hasScheduleEnded(schedule)) {
          const endTime = this.parseLocalDateTime(schedule.end_time || schedule.endTime);
          showToast(`Cannot generate QR code. Training has already ended.\nEnd time: ${endTime ? endTime.toLocaleString() : 'Unknown'}\nCurrent time: ${new Date().toLocaleString()}`, "error");
        }
        return;
      }

      if (!this.selectedTraining || !this.selectedTraining.trainingID) {
        showToast("Please select a training first.", "error");
        return;
      }

      // Check if QR is already active for this schedule
      const scheduleId = schedule.trainingScheduleID;
      
      // If QR is already active and displayed for this schedule, just open the modal
      if (activeTrainingQR.value && 
          activeTrainingId.value === this.selectedTraining.trainingID &&
          activeScheduleId.value === scheduleId) {
        // QR already active for this schedule, just show the modal
        this.openQRModal(schedule);
        return;
      }
      
      // If schedule already has an attendance_key (QR already generated), 
      // just fetch it from backend (which will return existing QR) and show modal
      // The backend will return the existing QR if it's already generated

      try {
        console.log("Sending QR generation request for schedule:", schedule.trainingScheduleID || 'No ID');
        const result = await generateQR(this.selectedTraining, schedule);
        
        if (result.success) {
          // QR code is now active - show it in the modal
          console.log("QR code generated successfully for schedule");
          this.openQRModal(schedule);
        } else {
          console.error("QR generation failed:", result.error);
          showToast(result.error || "Failed to generate QR code. Please try again.", "error");
        }
      } catch (error) {
        console.error("Error generating QR for schedule:", error);
        const errorMessage = error.response?.data?.message || error.message || "Failed to generate QR code. Please try again.";
        showToast(errorMessage, "error");
      }
    },

    /**
     * Open QR Modal
     */
    openQRModal(schedule = null) {
      if (!activeTrainingQR.value || activeTrainingId.value !== this.selectedTraining?.trainingID) {
        return;
      }

      // If a schedule is provided, use it; otherwise find the active schedule
      if (schedule) {
        this.activeScheduleForQR = schedule;
      } else if (activeScheduleId.value && this.selectedTraining?.schedules) {
        this.activeScheduleForQR = this.selectedTraining.schedules.find(
          s => s.trainingScheduleID === activeScheduleId.value
        );
      } else {
        // For backward compatibility with single schedule trainings
        if (this.selectedTraining?.schedule) {
          this.activeScheduleForQR = {
            schedule: this.selectedTraining.schedule,
            end_time: this.selectedTraining.end_time,
            mode: this.selectedTraining.mode,
            location: this.selectedTraining.location,
            trainingLink: this.selectedTraining.trainingLink
          };
        } else {
          this.activeScheduleForQR = null;
        }
      }
      this.showQRModal = true;
    },

    /**
     * Close QR Modal
     */
    closeQRModal() {
      this.showQRModal = false;
      this.activeScheduleForQR = null;
    },

    /**
     * Download QR Code as Image
     */
    async downloadQRCode() {
      if (!activeTrainingQR.value) return;
      
      try {
        const QRCode = await import('qrcode');
        const canvas = document.createElement('canvas');
        await QRCode.default.toCanvas(canvas, activeTrainingQR.value, {
          width: 500,
          margin: 2,
          color: {
            dark: '#000000',
            light: '#FFFFFF'
          }
        });
        
        canvas.toBlob((blob) => {
          const url = URL.createObjectURL(blob);
          const link = document.createElement('a');
          const trainingTitle = (this.selectedTraining.title || 'Training').replace(/[^a-z0-9]/gi, '_');
          link.href = url;
          link.download = `QR-Code-${trainingTitle}-${new Date().getTime()}.png`;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          URL.revokeObjectURL(url);
        }, 'image/png', 1.0);
      } catch (error) {
        console.error("Error downloading QR code:", error);
        showToast("Failed to download QR code. Please try again.", "error");
      }
    },

    /**
     * Check if a schedule has active QR code
     */
    isScheduleQRActive(schedule) {
      if (!schedule || !this.selectedTraining) return false;
      
      // For backward compatibility, check if it's a single schedule training
      if (!schedule.trainingScheduleID && schedule.schedule) {
        // Single schedule training - check if training QR is active
        return (
          activeTrainingId.value === this.selectedTraining.trainingID &&
          activeScheduleId.value === null &&
          activeTrainingQR.value !== null
        );
      }
      
      // Multiple schedules - check by schedule ID
      const scheduleId = schedule.trainingScheduleID;
      return (
        activeScheduleId.value === scheduleId &&
        activeTrainingId.value === this.selectedTraining.trainingID &&
        activeTrainingQR.value !== null
      );
    },

    /**
     * Check if a training is currently ongoing
     * A training is ongoing if it has at least one schedule that is active (started but not ended)
     */
    isTrainingOngoing(training) {
      if (!training) return false;
      
      const now = new Date();
      
      // Check if training has multiple schedules
      if (training.schedules && Array.isArray(training.schedules) && training.schedules.length > 0) {
        // Check if any schedule is currently active
        return training.schedules.some(schedule => {
          const scheduleTime = schedule.schedule || schedule.Schedule;
          const endTime = schedule.end_time || schedule.endTime;
          
          if (!scheduleTime || !endTime) return false;
          
          const startTime = this.parseLocalDateTime(scheduleTime);
          const endTimeDate = this.parseLocalDateTime(endTime);
          
          if (!startTime || !endTimeDate) return false;
          
          return now >= startTime && now < endTimeDate;
        });
      }
      
      // Single schedule format (backward compatibility)
      const scheduleTime = training.schedule || training.Schedule;
      const endTime = training.end_time || training.endTime;
      
      if (!scheduleTime || !endTime) return false;
      
      const startTime = this.parseLocalDateTime(scheduleTime);
      const endTimeDate = this.parseLocalDateTime(endTime);
      
      if (!startTime || !endTimeDate) return false;
      
      return now >= startTime && now < endTimeDate;
    },

  },


  mounted() {
    // Get organization logo from localStorage
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      try {
        const user = JSON.parse(storedUser);
        if (user.organization) {
          this.organizationLogo = user.organization;
          this.organizationStatus = user.organization.status || user.organization.Status || null;
        } else if (user.logo_directory || user.Logo_directory || user.logoPath) {
          this.organizationLogo = user;
        }
        if (!this.organizationStatus) {
          this.organizationStatus = user.status || user.Status || null;
        }
        this.isOrganizationVerified = ["approved", "verified"].includes(
          (this.organizationStatus || "").toString().toLowerCase()
        );
      } catch (error) {
        console.error("Error parsing user from localStorage:", error);
      }
    }
    this.fetchTrainings();
    this.fetchAllTrainings();
    document.addEventListener("click", this.handleOutsideClick);

    // Poll every 30 seconds to update trainings
    this.trainingPollInterval = setInterval(() => {
      this.fetchTrainings();
      this.fetchAllTrainings();
    }, 30000);
  },

  beforeUnmount() {
    document.removeEventListener("click", this.handleOutsideClick);
  },

  computed: {
    currentOrganizationId() {
      const storedUser = localStorage.getItem("user");
      if (storedUser) {
        try {
          const parsedUser = JSON.parse(storedUser);
          return parsedUser?.organizationID ?? parsedUser?.organization?.organizationID ?? null;
        } catch (error) {
          console.error("Error parsing user from localStorage:", error);
        }
      }
      return null;
    },
    organizationStatusLabel() {
      return this.isOrganizationVerified ? "Verified" : "Unverified";
    },
    organizationStatusClass() {
      return this.isOrganizationVerified ? "org-status-verified" : "org-status-unverified";
    },
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

    sortedOngoingTrainings() {
      const orgId = this.currentOrganizationId;
      return this.upcomingtrainings
        .filter(t => {
          // Filter by organization ID if available
          if (orgId && t.organization_id !== orgId && t.organizationID !== orgId) {
            return false;
          }
          return true;
        })
        .filter(t => this.isTrainingOngoing(t))
        .sort((a, b) => {
          // Sort by earliest active schedule
          const aSchedule = this.getEarliestActiveSchedule(a);
          const bSchedule = this.getEarliestActiveSchedule(b);
          if (!aSchedule || !bSchedule) return 0;
          const aTime = this.parseLocalDateTime(aSchedule.schedule || aSchedule.Schedule);
          const bTime = this.parseLocalDateTime(bSchedule.schedule || bSchedule.Schedule);
          if (!aTime || !bTime) return 0;
          return aTime - bTime;
        });
    },

    sortedUpcomingTrainings() {
      const orgId = this.currentOrganizationId;
      const now = new Date();
      
      // Show trainings that:
      // 1. Are not currently ongoing
      // 2. Have at least one schedule that hasn't started yet (future schedule exists)
      // This includes trainings between schedules where previous schedules have ended but future ones are coming
      return this.upcomingtrainings
        .filter(t => {
          // Filter by organization ID if available
          if (orgId && t.organization_id !== orgId && t.organizationID !== orgId) {
            return false;
          }
          
          // Exclude ongoing trainings
          if (this.isTrainingOngoing(t)) {
            return false;
          }
          
          // Check if training has multiple schedules
          if (t.schedules && Array.isArray(t.schedules) && t.schedules.length > 0) {
            // Check if there's at least one schedule that hasn't started yet
            const hasFutureSchedule = t.schedules.some(schedule => {
              const scheduleTime = schedule.schedule || schedule.Schedule;
              if (!scheduleTime) return false;
              const startTime = this.parseLocalDateTime(scheduleTime);
              return startTime && startTime.getTime() > now.getTime();
            });
            return hasFutureSchedule; // Show if any schedule is in the future
          }
          
          // Single schedule format - check if it hasn't started yet
          const scheduleTime = t.schedule || t.Schedule;
          if (!scheduleTime) return false;
          const startTime = this.parseLocalDateTime(scheduleTime);
          return startTime && startTime.getTime() > now.getTime();
        })
        .sort((a, b) => {
          // Sort by earliest upcoming schedule
          const getEarliestUpcomingSchedule = (training) => {
            if (training.schedules && Array.isArray(training.schedules) && training.schedules.length > 0) {
              let earliest = null;
              for (const schedule of training.schedules) {
                const scheduleTime = schedule.schedule || schedule.Schedule;
                if (scheduleTime) {
                  const startTime = this.parseLocalDateTime(scheduleTime);
                  if (startTime && startTime.getTime() > now.getTime()) {
                    if (!earliest || startTime < earliest) {
                      earliest = startTime;
                    }
                  }
                }
              }
              return earliest;
            }
            const scheduleTime = training.schedule || training.Schedule;
            return scheduleTime ? this.parseLocalDateTime(scheduleTime) : null;
          };
          
          const aTime = getEarliestUpcomingSchedule(a);
          const bTime = getEarliestUpcomingSchedule(b);
          if (!aTime || !bTime) return 0;
          return aTime.getTime() - bTime.getTime();
        });
    },

    sortedCompletedTrainings() {
      const now = new Date();
      const orgId = this.currentOrganizationId;
      
      // Helper function to get the latest end time for a training
      const getLatestEndTime = (training) => {
        if (training.schedules && Array.isArray(training.schedules) && training.schedules.length > 0) {
          let latest = null;
          for (const schedule of training.schedules) {
            const endTime = schedule.end_time || schedule.endTime || schedule.endTimeDate;
            if (endTime) {
              const endTimeDate = this.parseLocalDateTime(endTime);
              if (endTimeDate && (!latest || endTimeDate.getTime() > latest.getTime())) {
                latest = endTimeDate;
              }
            }
          }
          return latest;
        }
        // Fallback to top-level end_time for single schedule trainings
        const endTime = training.end_time || training.endTime || training.endTimeDate;
        return endTime ? this.parseLocalDateTime(endTime) : null;
      };
      
      // A training is completed if:
      // 1. It belongs to this organization (if orgId is set)
      // 2. It's not currently ongoing
      // 3. All schedules have ended (no schedules in the future, and latest end time has passed)
      const completedTrainings = this.upcomingtrainings
        .filter(t => {
          // Filter by organization ID if available
          if (orgId) {
            const trainingOrgId = t.organizationID || t.organization_id;
            if (!trainingOrgId || trainingOrgId !== orgId) {
              return false;
            }
          }
          
          // Exclude ongoing trainings
          if (this.isTrainingOngoing(t)) {
            return false;
          }
          
          // Check if training has multiple schedules
          if (t.schedules && Array.isArray(t.schedules) && t.schedules.length > 0) {
            // For multiple schedules, training is completed if:
            // 1. ALL schedules have ended (latest end time has passed)
            // 2. NO schedules are in the future (all schedules have started and ended)
            
            let latestEndTime = null;
            let hasFutureSchedule = false;
            
            // Check all schedules
            for (const schedule of t.schedules) {
              // Check if this schedule hasn't started yet (future schedule)
              const scheduleTime = schedule.schedule || schedule.Schedule;
              if (scheduleTime) {
                const startTime = this.parseLocalDateTime(scheduleTime);
                if (startTime && startTime.getTime() > now.getTime()) {
                  hasFutureSchedule = true;
                  break; // Found a future schedule, so training is not completed
                }
              }
              
              // Track latest end time
              const endTime = schedule.end_time || schedule.endTime || schedule.endTimeDate;
              if (endTime) {
                const endTimeDate = this.parseLocalDateTime(endTime);
                if (endTimeDate && (!latestEndTime || endTimeDate.getTime() > latestEndTime.getTime())) {
                  latestEndTime = endTimeDate;
                }
              }
            }
            
            // If there's a future schedule, it's not completed (it's upcoming)
            if (hasFutureSchedule) {
              return false;
            }
            
            // No future schedules, check if latest end time has passed
            // This means ALL schedules have ended
            if (latestEndTime) {
              return latestEndTime.getTime() <= now.getTime();
            }
            
            return false;
          } else {
            // Single schedule format
            const scheduleTime = t.schedule || t.Schedule;
            const endTime = t.end_time || t.endTime || t.endTimeDate;
            
            if (!scheduleTime) {
              return false;
            }
            
            const startTime = this.parseLocalDateTime(scheduleTime);
            
            // If schedule hasn't started yet, it's upcoming
            if (startTime && startTime.getTime() > now.getTime()) {
              return false;
            }
            
            // Schedule has started, check if it has ended
            if (endTime) {
              const endTimeDate = this.parseLocalDateTime(endTime);
              if (endTimeDate) {
                return endTimeDate.getTime() <= now.getTime();
              }
            }
            
            // Fallback: if no end_time, check if schedule was more than 1 day ago
            if (startTime) {
              const oneDayAgo = new Date(now.getTime() - 24 * 60 * 60 * 1000);
              return startTime.getTime() < oneDayAgo.getTime();
            }
            
            return false;
          }
        })
        .sort((a, b) => {
          // Sort by latest end time (most recent first)
          const aEndTime = getLatestEndTime(a);
          const bEndTime = getLatestEndTime(b);
          if (!aEndTime || !bEndTime) return 0;
          return bEndTime.getTime() - aEndTime.getTime(); // Descending order (most recent first)
        });
      
      // Enhanced debug logging to understand why trainings aren't showing
      if (completedTrainings.length === 0 && this.upcomingtrainings.length > 0) {
        const debugInfo = this.upcomingtrainings.map(t => {
          const trainingOrgId = t.organizationID || t.organization_id;
          const isOrgMatch = !orgId || trainingOrgId === orgId;
          const isOngoing = this.isTrainingOngoing(t);
          const latestEndTime = getLatestEndTime(t);
          const latestEndTimeStr = latestEndTime ? latestEndTime.toISOString() : 'N/A';
          const latestEndLocalStr = latestEndTime ? latestEndTime.toLocaleString() : 'N/A';
          const nowLocalStr = now.toLocaleString();
          const isEndTimePassed = latestEndTime ? latestEndTime.getTime() <= now.getTime() : false;
          
          let hasFutureSchedule = false;
          if (t.schedules && Array.isArray(t.schedules) && t.schedules.length > 0) {
            hasFutureSchedule = t.schedules.some(schedule => {
              const scheduleTime = schedule.schedule || schedule.Schedule;
              if (scheduleTime) {
                const startTime = this.parseLocalDateTime(scheduleTime);
                return startTime && startTime.getTime() > now.getTime();
              }
              return false;
            });
          }
          
          return {
            id: t.trainingID,
            title: t.title,
            orgId: trainingOrgId,
            isOrgMatch: isOrgMatch,
            isOngoing: isOngoing,
            hasFutureSchedule: hasFutureSchedule,
            latestEndTime: latestEndTimeStr,
            latestEndTimeLocal: latestEndLocalStr,
            currentTimeLocal: nowLocalStr,
            isEndTimePassed: isEndTimePassed,
            schedules: t.schedules?.map(s => ({
              start: s.schedule,
              end: s.end_time,
              startParsed: this.parseLocalDateTime(s.schedule)?.toLocaleString(),
              endParsed: this.parseLocalDateTime(s.end_time)?.toLocaleString()
            })) || [{ start: t.schedule, end: t.end_time }]
          };
        });
        
        console.log("🔍 Debug: Completed Trainings Analysis", {
          totalTrainings: this.upcomingtrainings.length,
          completedCount: completedTrainings.length,
          orgId: orgId,
          currentTimeUTC: now.toISOString(),
          currentTimeLocal: now.toLocaleString(),
          trainings: debugInfo
        });
      }
      
      return completedTrainings;
    },
    filteredUpcoming() {
      const query = this.globalSearchQuery.toLowerCase();
      if (!query) return this.sortedUpcomingTrainings;
      return this.sortedUpcomingTrainings.filter(training =>
        training.title.toLowerCase().startsWith(query) // 🔹 only matches if letters typed are in order from the start
      );
    },
    filteredOngoing() {
      const query = this.globalSearchQuery.toLowerCase();
      if (!query) return this.sortedOngoingTrainings;
      return this.sortedOngoingTrainings.filter(training =>
        training.title.toLowerCase().startsWith(query)
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
    visibleFilteredOngoing() {
      return this.showAllOngoing
        ? this.filteredOngoing
        : this.filteredOngoing.slice(0, 4);
    },
    visibleFilteredCompleted() {
      return this.showAllCompleted
        ? this.filteredCompleted
        : this.filteredCompleted.slice(0, 4);
    },
    sortedAllTrainings() {
      const now = new Date();
      return this.allTrainings
        .sort((a, b) => {
          // Sort by earliest schedule
          const getEarliestSchedule = (training) => {
            if (training.schedules && Array.isArray(training.schedules) && training.schedules.length > 0) {
              let earliest = null;
              for (const schedule of training.schedules) {
                const scheduleTime = schedule.schedule || schedule.Schedule;
                if (scheduleTime) {
                  const startTime = this.parseLocalDateTime(scheduleTime);
                  if (startTime && (!earliest || startTime < earliest)) {
                    earliest = startTime;
                  }
                }
              }
              return earliest;
            }
            const scheduleTime = training.schedule || training.Schedule;
            return scheduleTime ? this.parseLocalDateTime(scheduleTime) : null;
          };
          
          const aTime = getEarliestSchedule(a);
          const bTime = getEarliestSchedule(b);
          if (!aTime || !bTime) return 0;
          return aTime.getTime() - bTime.getTime();
        });
    },
    filteredAllTrainings() {
      const query = this.globalSearchQuery.toLowerCase();
      if (!query) return this.sortedAllTrainings;
      return this.sortedAllTrainings.filter(training =>
        training.title.toLowerCase().startsWith(query)
      );
    },
    visibleFilteredAllTrainings() {
      return this.showAllTrainings
        ? this.filteredAllTrainings
        : this.filteredAllTrainings.slice(0, 4);
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

    // Filter tags based on search input
    filteredTags() {
      if (!this.newTagName || this.newTagName.trim() === "") {
        return this.tagOptions;
      }
      const searchQuery = this.newTagName.toLowerCase().trim();
      return this.tagOptions.filter(tag =>
        tag.TagName.toLowerCase().includes(searchQuery)
      );
    },
    // Filter registrants based on search query
    filteredRegistrants() {
      if (!this.registrantSearchQuery || this.registrantSearchQuery.trim() === "") {
        return this.registrantsList;
      }
      const query = this.registrantSearchQuery.toLowerCase().trim();
      return this.registrantsList.filter(registrant => {
        const name = (registrant.name || "").toLowerCase();
        const id = String(registrant.id || "").toLowerCase();
        const status = (registrant.status || "").toLowerCase();
        return name.includes(query) || id.includes(query) || status.includes(query);
      });
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

.modal-title-row {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
  flex-wrap: wrap;
}

.modal-title-row .modal-title {
  margin: 0;
}

.modal-tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 8px 0 16px;
}

.modal-tag-chip {
  background-color: #d8e3f0;
  color: #1f2a37;
  font-size: 0.85rem;
  padding: 4px 10px;
  border-radius: 999px;
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

.avatar {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background-color: #ccc;
  /* Placeholder, replace with image if needed */
  margin: 20px auto 10px auto;
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

.org-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.org-status-icon {
  width: 14px;
  height: 14px;
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

.ongoing {
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

.cert-buttons {
  display: flex;
  gap: 6px;
}

.view-cert-btn {
  background-color: #0059b3;
  color: white;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  transition: 0.2s ease;
}

.view-cert-btn:hover {
  background-color: #004a99;
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

.bulk-issue-btn {
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

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background-color: #d1d5db !important;
  color: #9ca3af !important;
}

.action-btn.disabled-action:not(:disabled) {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
  background-color: #d1d5db !important;
  color: #9ca3af !important;
}

/* Tooltip wrapper for disabled buttons */
.button-tooltip-wrapper {
  display: inline-block;
  position: relative;
  pointer-events: auto;
}

.button-tooltip-wrapper:has(button:disabled) {
  cursor: not-allowed;
}

.button-tooltip-wrapper button:disabled {
  pointer-events: none;
}

/* Custom tooltip that works on disabled buttons */
.button-tooltip-wrapper[title]:hover::after {
  content: attr(title);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  margin-bottom: 8px;
  padding: 8px 12px;
  background-color: #1f2937;
  color: white;
  border-radius: 6px;
  font-size: 13px;
  white-space: nowrap;
  z-index: 10000;
  pointer-events: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  animation: tooltipFadeIn 0.2s ease;
}

.button-tooltip-wrapper[title]:hover::before {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  margin-bottom: 2px;
  border: 6px solid transparent;
  border-top-color: #1f2937;
  z-index: 10001;
  pointer-events: none;
  animation: tooltipFadeIn 0.2s ease;
}

@keyframes tooltipFadeIn {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
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
  padding: 20px;
}

.training-popup {
  background: #f9fafb;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  width: min(95vw, 800px);
  max-width: 800px;
  max-height: 90vh;
  padding: 24px;
  position: relative;
  overflow-y: auto;
  overflow-x: hidden;
}

.training-popup-close {
  position: absolute;
  top: 10px;
  right: 12px;
  background: rgba(249, 250, 251, 0.95);
  border: none;
  font-size: 18px;
  color: #555;
  cursor: pointer;
  z-index: 10;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background 0.2s;
}

.training-popup-close:hover {
  background: rgba(249, 250, 251, 1);
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
  padding-top: 10px;
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

.training-post-btn:disabled,
.plus-btn-text:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

.disabled-action {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
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
  padding: 20px;
}

.training-popup {
  background: #f9fafb;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  width: min(95vw, 800px);
  max-width: 800px;
  max-height: 90vh;
  padding: 24px;
  position: relative;
  overflow-y: auto;
  overflow-x: hidden;
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
  z-index: 10;
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
  padding-top: 10px;
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
  display: flex;
  gap: 10px;
  align-items: center;
}

.schedule-input-wrapper input[type="date"] {
  flex: 1;
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

.add-date-btn {
  padding: 10px 20px;
  background: #4c6ef5;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}

.add-date-btn:hover:not(:disabled) {
  background: #3b5bdb;
}

.add-date-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.selected-dates-list {
  margin-top: 15px;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.schedule-item {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 15px;
  background: #f9fafb;
}

.schedule-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e5e7eb;
}

.schedule-date {
  font-weight: 600;
  font-size: 15px;
  color: #374151;
}

.remove-schedule-btn {
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  transition: background 0.2s;
}

.remove-schedule-btn:hover {
  background: #dc2626;
}

.schedule-item-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.time-inputs-row {
  display: flex;
  flex-direction: row;
  gap: 12px;
  align-items: flex-start;
}

.time-inputs-row .time-input-wrapper {
  flex: 1;
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
  width: min(95vw, 900px);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  animation: fadeIn 0.25s ease;
  z-index: 2100;
  overflow: hidden;
  /* ensure above other overlays */
}

.training-details-body {
  flex: 0 0 auto;
  overflow: visible;
  padding-right: 0.5rem;
}

.training-info {
  margin: 0.4rem 0;
  color: #333;
}

/* Schedule Cards Container */
.schedules-container {
  margin: 0.75rem 0;
}

.schedules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.75rem;
  margin-top: 0.5rem;
}

/* Schedule Card Wrapper */
.schedule-card-wrapper {
  position: relative;
  display: flex;
  flex-direction: column;
}

.schedule-card {
  background: #f5f5f5;
  border-radius: 10px;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  transition: all 0.3s ease;
  cursor: default;
  border: 1px solid transparent;
  position: relative;
}

.schedule-card:hover {
  background: #e8e8e8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #d1d5db;
}

/* Clickable Schedule Card */
.schedule-card-clickable {
  cursor: pointer;
  border-color: #3b82f6;
}

.schedule-card-clickable:hover {
  background: #dbeafe;
  border-color: #2563eb;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Disabled Schedule Card */
.schedule-card-disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.schedule-card-disabled:hover {
  background: #f5f5f5;
  transform: none;
  border-color: #e5e7eb;
  box-shadow: none;
}

/* Active QR Schedule Card */
.schedule-card-active-qr {
  border-color: #10b981;
  background: #d1fae5;
}

.schedule-card-active-qr:hover {
  background: #a7f3d0;
  border-color: #059669;
}

/* Hover Tooltip Below Card */
.schedule-hover-tooltip-below {
  margin-top: 0.5rem;
  background-color: #1f2937;
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  text-align: center;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  position: relative;
  line-height: 1.4;
  word-wrap: break-word;
  max-width: 100%;
}

.schedule-hover-tooltip-below::before {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 6px solid transparent;
  border-bottom-color: #1f2937;
}

.schedule-card-wrapper:hover .schedule-hover-tooltip-below {
  opacity: 1;
}

.schedule-date-time {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #333;
  font-size: 0.85rem;
}

.schedule-icon {
  width: 16px;
  height: 16px;
  color: #60a5fa;
  flex-shrink: 0;
}

.schedule-mode-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.35rem 0.6rem;
  border-radius: 6px;
  border: 2px solid;
  width: fit-content;
  font-size: 0.8rem;
  font-weight: 500;
}

.mode-onsite {
  background-color: #dbeafe;
  border-color: #3b82f6;
  color: #1e40af;
}

.mode-online {
  background-color: #fef3c7;
  border-color: #f59e0b;
  color: #92400e;
}

.mode-icon {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

.schedule-location,
.schedule-link {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #333;
  font-size: 0.85rem;
}

.schedule-link .training-link {
  color: #3b82f6;
  text-decoration: underline;
  word-break: break-all;
}

.schedule-link .training-link:hover {
  color: #2563eb;
}

.training-description {
  margin-top: 1rem;
  color: #555;
  line-height: 1.5;
}

.registrants-section {
  margin-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding-right: 0.5rem;
}

.registrants-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.registrants-header h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.registrants-search-wrapper {
  margin-bottom: 1rem;
}

.registrants-search-input {
  width: 100%;
  padding: 10px 15px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: #ffffff;
  color: #111827;
  outline: none;
  transition: border-color 0.2s;
}

.registrants-search-input:focus {
  border-color: #4c6ef5;
  box-shadow: 0 0 0 3px rgba(76, 110, 245, 0.1);
}

.registrants-search-input::placeholder {
  color: #9ca3af;
}

.registrants-refresh-btn {
  background-color: #374151;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.registrants-refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.registrants-refresh-btn:not(:disabled):hover {
  background-color: #1f2937;
}

.registrants-error {
  color: #b91c1c;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
}

.registrants-loading,
.registrants-empty {
  font-size: 0.9rem;
  color: #4b5563;
  margin-bottom: 0.75rem;
}

.registrants-table-container.inline {
  max-height: none;
  overflow-y: visible;
}

.registrants-footer {
  margin-top: 0.75rem;
  display: flex;
  justify-content: flex-end;
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
  transform: translateX(170px);
  /* Adjust this to your sidebar width (230px - 60px = 170px) */
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

/* Character Counter Styles */
.input-with-counter {
  position: relative;
  width: 100%;
  margin-bottom: 15px;
}

.char-counter {
  font-size: 12px;
  color: #6b7280;
  text-align: right;
  margin-top: 4px;
  display: block;
}

.char-counter-search {
  position: absolute;
  right: 10px;
  bottom: 8px;
  font-size: 12px;
  color: #6b7280;
  background-color: rgba(255, 255, 255, 0.9);
  padding: 2px 4px;
  border-radius: 4px;
}

/* QR Code Modal */
.qr-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 3000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.qr-modal {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  width: min(95vw, 500px);
  max-width: 500px;
  position: relative;
  animation: fadeIn 0.25s ease;
  overflow: hidden;
}

.qr-modal-close {
  position: absolute;
  top: 15px;
  right: 15px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  font-size: 24px;
  color: #666;
  cursor: pointer;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  z-index: 10;
}

.qr-modal-close:hover {
  background: #f3f4f6;
  color: #000;
  transform: scale(1.1);
}

.qr-modal-content {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.qr-modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  text-align: center;
  margin: 0;
  padding-right: 2rem; /* Space for close button */
}

.qr-modal-schedule-info {
  text-align: center;
  padding: 0.75rem 1rem;
  background-color: #f9fafb;
  border-radius: 8px;
  width: 100%;
}

.qr-schedule-date {
  font-size: 0.95rem;
  color: #374151;
  margin: 0 0 0.5rem 0;
  font-weight: 500;
}

.qr-schedule-mode {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  margin: 0;
}

.qr-schedule-mode.mode-onsite {
  background-color: #dbeafe;
  color: #1e40af;
  border: 1px solid #3b82f6;
}

.qr-schedule-mode.mode-online {
  background-color: #fef3c7;
  color: #92400e;
  border: 1px solid #f59e0b;
}

.qr-code-wrapper {
  padding: 1rem;
  background-color: #fff;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-modal-info {
  text-align: center;
  width: 100%;
}

.qr-expiry-info {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0 0 1rem 0;
}

.qr-expiry-info strong {
  color: #374151;
}

.qr-instructions {
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.6;
  margin: 0;
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 8px;
  border-left: 4px solid #3b82f6;
}

.qr-instructions strong {
  color: #374151;
}

.qr-modal-download-btn {
  background-color: #374151;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
  margin-top: 0.5rem;
}

.qr-modal-download-btn:hover {
  background-color: #1f2937;
}

.loader {
  border: 4px solid rgba(0,0,0,0.1);
  border-left-color: #4f46e5; /* Tailwind indigo-600 */
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
