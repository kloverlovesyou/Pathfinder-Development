<template>
  <div class="organization-trainings">
    <!-- Sidebar -->
    <transition name="slide">
      <aside class="sidebar" :class="{ collapsed: !isSidebarOpen }" @click.self="toggleSidebar">
        <!-- Avatar always visible -->
        <div class="avatar">
          <img :src="dictLogo" alt="DICT Logo" class="avatar-img" />
        </div>

        <!-- Profile Section (only when sidebar is open) -->
        <transition name="fade">
          <div v-if="isSidebarOpen" class="profile-section">
            <h3 class="org-name">{{ name }}</h3>
            <div class="profile-actions">
              <div class="action" @click="$router.push('/updateprofile')">
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
          <svg
            width="30"
            height="30"
            viewBox="0 0 30 30"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
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
          <svg
            width="29"
            height="29"
            viewBox="0 0 29 29"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M20.5837 3.625C23.4119 3.625 24.8261 3.62526 25.7048 4.50391C26.5833 5.3826 26.5837 6.79675 26.5837 9.625V19.375C26.5837 22.2033 26.5833 23.6174 25.7048 24.4961C24.8261 25.3747 23.4119 25.375 20.5837 25.375H8.41666C5.58824 25.375 4.17425 25.3748 3.29557 24.4961C2.41689 23.6174 2.41666 22.2034 2.41666 19.375V9.625C2.41666 6.79657 2.41689 5.38259 3.29557 4.50391C4.17425 3.62523 5.58824 3.625 8.41666 3.625H20.5837ZM9.66666 12.292C9.11438 12.292 8.66666 12.7397 8.66666 13.292V20.542L8.67155 20.6445C8.72303 21.1485 9.1491 21.542 9.66666 21.542C10.1842 21.542 10.6103 21.1485 10.6618 20.6445L10.6667 20.542V13.292C10.6667 12.7397 10.2189 12.292 9.66666 12.292ZM19.3337 9.875C18.7814 9.875 18.3337 10.3227 18.3337 10.875V20.542L18.3385 20.6436C18.3896 21.148 18.8158 21.542 19.3337 21.542C19.8514 21.5418 20.2778 21.1479 20.3288 20.6436L20.3337 20.542V10.875C20.3337 10.3228 19.8858 9.87518 19.3337 9.875ZM14.4997 14.708C13.9476 14.7082 13.4998 15.156 13.4997 15.708V20.541L13.5046 20.6436C13.5557 21.1477 13.982 21.5408 14.4997 21.541C15.0175 21.541 15.4436 21.1478 15.4948 20.6436L15.4997 20.541V15.708C15.4995 15.1559 15.0518 14.708 14.4997 14.708Z"
              fill="white" />
          </svg>
          <span>Trainings</span>
        </div>
        <div class="icon"  @click="navigateTo({ name: 'OrgCareers' })">
          <svg
            width="25"
            height="25"
            viewBox="0 0 25 25"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
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
          <svg
            width="26"
            height="26"
            viewBox="0 0 26 26"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
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
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="28"
            height="28"
            viewBox="0 0 24 24"
            fill="none"
            stroke="white"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
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

      <!-- Insert job picks style block here -->
      <section class="upcoming">
        <div class="flex items-center justify-between">
          <!-- Left side: title + count -->
          <h2 class="section-title flex items-center gap-1">
            On-Going Careers
            <span class="count-badge">{{ upcomingCareers.length }}</span>
          </h2>

          <!-- Right side: plain plus -->
          <button class="plus-btn-text" @click="openCareerPopup">+</button>
        </div>

        <div class="career-slider">
          <div class="career-card" v-for="career in upcomingCareers" :key="career.careerID">
            <div class="career-left">
            </div>

            <div class="career-right">
              <h3 class="career-title">{{ career.position }}</h3>
            </div>

            <!-- 3-dot menu -->
            <div class="menu">
              <div class="menu-icon" @click="toggleUpcomingMenu(career.careerID)">
                ⋮
              </div>
              <div v-if="openUpcomingMenu === career.careerID" class="dropdown-menu">
                <ul>
                  <li @click="openRegistrantsModal">Applicants</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="completed">
        <div class="flex items-center justify-between">
          <h2 class="section-title flex items-center gap-1">
            Filled Out Careers
            <span class="count-badge">{{ completedCareers.length }}</span>
          </h2>
        </div>
        <div class="career-slider">
          <div class="career-card" v-for="career in completedCareers" :key="career.id">
            <div class="career-left">
            </div>

            <div class="career-right">
              <h3 class="career-title">{{ career.title }}</h3>
            </div>

            <!-- 3-dot menu -->
            <div class="menu">
              <div class="menu-icon" @click="toggleCompletedMenu(career.id)">
                ⋮
              </div>
              <div v-if="openCompletedMenu === career.id" class="dropdown-menu">
                <ul>
                  <li @click="openRegistrantsModal">Applicants</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- Registrants Modal -->
      <div v-if="showRegistrantsModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
          <button class="modal-close-btn" @click="closeModal">✕</button>
          <h3 class="modal-title">List of Registrants</h3>
          <div class="registrants-grid">
            <div v-for="person in registrantsList" :key="person.id" class="registrant-card">
              <img :src="person.img" alt="profile" class="profile-pic" />
              <p>{{ person.name }}</p>
            </div>
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
          <form @submit.prevent="saveCareer" class="Caeer-popup-form">
            <input v-model="newCareer.position" type="text" placeholder="Position" class="career-input" />
            <input v-model="newCareer.details" type="text" placeholder="Details and Instruction" class="career-input" />
            <textarea v-model="newCareer.qualifications" placeholder="Qualifications" class="career-input"></textarea>
            <textarea v-model="newCareer.requirements" placeholder="Requirements" class="career-input"></textarea>
            <input v-model="newCareer.letterAddress" type="text" placeholder="Application Letter Address"
              class="career-input" />
            <input v-model="newCareer.deadline" type="text" placeholder="Deadline of Submission" class="career-input" />

            <!-- Save -->
            <button type="submit" class="career-save-btn">Save</button>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import dictLogo from "@/assets/images/DICT-Logo-icon_only (1).png";
import axios from 'axios'; // Add this import

export default {
  data() {
    return {
      dictLogo,
      openUpcomingMenu: null,
      openCompletedMenu: null,

      registrantsList: [
        { id: 1, name: "John Doe", img: "https://i.pravatar.cc/100?img=1" },
        { id: 2, name: "Maria Santos", img: "https://i.pravatar.cc/100?img=2" },
        { id: 3, name: "David Cruz", img: "https://i.pravatar.cc/100?img=3" },
        { id: 4, name: "Anna Lee", img: "https://i.pravatar.cc/100?img=4" },
        { id: 5, name: "Mark Reyes", img: "https://i.pravatar.cc/100?img=5" },
        { id: 6, name: "Sophia Tan", img: "https://i.pravatar.cc/100?img=6" },
        { id: 7, name: "James Lim", img: "https://i.pravatar.cc/100?img=7" },
        {
          id: 8,
          name: "Christine Dela Cruz",
          img: "https://i.pravatar.cc/100?img=8",
        },
        {
          id: 9,
          name: "Robert Mendoza",
          img: "https://i.pravatar.cc/100?img=9",
        },
        {
          id: 10,
          name: "Isabella Garcia",
          img: "https://i.pravatar.cc/100?img=10",
        },
      ],
      showRegistrantsModal: false,

      upcomingCareers: [],

      completedCareers: [
        { id: 1, title: "HR Specialist" },
        { id: 2, title: "Graphic Designer" },
        { id: 3, title: "Accountant" },
      ],

      // Popup state + form
      showCareerPopup: false,
      newCareer: {
        position: "",
        details: "",
        qualifications: "",
        requirements: "",
        letterAddress: "",
        deadline: "",
      },
      upcomingCareers: [],
    };
  },
  methods: {
    toggleUpcomingMenu(id) {
      this.openUpcomingMenu = this.openUpcomingMenu === id ? null : id;
    },
    toggleCompletedMenu(id) {
      this.openCompletedMenu = this.openCompletedMenu === id ? null : id;
    },
    openRegistrantsModal() {
      this.showRegistrantsModal = true;
    },
    closeModal() {
      this.showRegistrantsModal = false;
    },


    //fetch careers
    async fetchCareers(){
      try{
        const response = await axios.get("http://127.0.0.1:8000/api/careers");
        this.upcomingCareers = response.data;
      } catch(error){
        console.error("ERROR FETCHING CAREERS:", error);
      }
    },

    // Popup methods
    openCareerPopup() {
      this.showCareerPopup = true;
    },
    closeCareerPopup() {
      this.showCareerPopup = false;
      this.resetNewCareer();
    },

    // Save button function
    async saveCareer() {
      try {
        // Basic validation
        if (!this.newCareer.position || !this.newCareer.details || !this.newCareer.qualifications || !this.newCareer.requirements || !this.newCareer.letterAddress || !this.newCareer.deadline) {
          alert("PLEASE FILL IN ALL REQUIRED FIELDS");
          return;
        }

        //payload matching controller
        const payload = {
          position: this.newCareer.position,
          details: this.newCareer.details,
          qualifications: this.newCareer.qualifications,
          requirements: this.newCareer.requirements,
          letterAddress: this.newCareer.letterAddress,
          deadline: this.newCareer.deadline
        };

        //just a debug log, remove later
        console.log("PAYLOAD BEING SENT TO BACKEND: ", payload);

        //send to API
        const token = localStorage.getItem('token');
        const response = await axios.post("http://127.0.0.1:8000/api/careers", payload, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        console.log("CAREER SAVED:", response.data);

        //add it to the upcoming careers
        if(response.data && response.data.data){
          const newCareer = response.data.data;

          //get organization name from localStorage
          const storedUser = localStorage.getItem("user");
          let organizationName = "Unknown Organization";
          if(storedUser){
            const user = JSON.parse(storedUser);
            organizationName = user.displayName || user.name || "Unknown Organization";
          }

          // Parse the deadline to separate date and time
          const deadlineDate = new Date(newCareer.deadlineOfSubmission);
          const formattedDate = deadlineDate.toLocaleDateString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
          });
          const formattedTime = deadlineDate.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
          });

          this.upcomingCareers.push({
            id: newCareer.careerID,
            title: newCareer.position,
            position: newCareer.position,
            details: newCareer.detailsAndInstructions,
            qualifications: newCareer.qualifications,
            requirements: newCareer.requirements,
            letterAddress: newCareer.applicationLetterAddress,
            deadline: formattedDate,
            time: formattedTime,
            organizationName: organizationName
          })
        }

        alert("CAREER POSTED SUCCESSFULLY!!!");

        //reset form
        this.resetNewCareer();
        this.showCareerPopup = false;
      } catch (error) {
        console.error("ERROR SAVING CAREER:", error.response?.data || error);
        alert("SOMETHING WENT WRONG WHILE SAVING THE CAREER");
      }
    },

    resetNewCareer() {
      this.newCareer = {
        position: "",
        details: "",
        qualifications: "",
        requirements: "",
        letterAddress: "",
      };
    },
  },

  mounted(){
    this.fetchCareers();
  }


}
</script>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from 'vue-router';

const router = useRouter();
const isSidebarOpen = ref(true);
const organizationName = ref("");

// Toggle sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

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
  font-family: "Segoe UI", sans-serif;
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
  font-family: "Segoe UI", sans-serif;
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
  display: flex;
  gap: 16px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  padding: 10px 0;
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #edf2f7;
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
  flex: 0 0 auto;
  width: 350px;
  display: flex;
  align-items: flex-start;
  background: white;
  border-radius: 10px;
  padding: 15px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: relative;
  scroll-snap-align: start;
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
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-content {
  position: relative;
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 500px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

.modal-title{
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
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
.career-radio-group {
  display: flex;
  gap: 20px;
  font-size: 14px;
  color: #374151;
}

.career-save-btn {
  background: #374151;
  color: white;
  font-size: 14px;
  font-weight: 500;
  padding: 10px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.career-save-btn:hover {
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
  font-weight: bold;
  line-height: 1;
  color: #374151;
  cursor: pointer;
  transition: color 0.2s;
}

.plus-btn-text:hover {
  color: #000000;
}
</style>
