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

        <div class="spacer"></div> <!-- pushes signout down -->
        <div class="icon signout" @click="$router.push('/loginform')">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

      <!-- Calendar Section -->
      <section class="calendar-section">
        <div class="calendar-header">
          <button @click="prevMonth">‹</button>
          <h2>{{ currentMonthName }} {{ currentYear }}</h2>
          <button @click="nextMonth">›</button>
        </div>

        <div class="calendar-grid">
          <div class="day-name" v-for="day in dayNames" :key="day">{{ day }}</div>

          <div v-for="(date, index) in calendarDays" :key="index" class="day-cell" :class="{
            today: isToday(date),
            event: hasEvent(date)
          }" :style="{ backgroundColor: getEventColor(date) }" @click="openDayModal(date)">
            <span v-if="date" class="date-number">{{ date.getDate() }}</span>
            <div v-if="date && getEventTitles(date).length" class="events-list">
              <div v-for="(title, i) in getEventTitles(date)" :key="i" class="event-title">
                {{ title }}
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Calendar Day Details Modal -->
      <div v-if="showModal" class="calendar-modal-overlay" @click.self="closeModal">
        <div class="calendar-modal-content">
          <!-- Header Row: Title + 3-dot menu -->
          <div class="calendar-modal-header">
            <h3>{{ formattedSelectedDate }}</h3>

            <!-- 3-Dot Dropdown Menu -->
            <div class="calendar-modal-menu">
              <button class="calendar-modal-menu-btn" @click.stop="toggleMenu">
                ⋮
              </button>

              <div v-if="showMenu" class="calendar-modal-dropdown">
                <button @click="editEvent" class="dropdown-item">Edit</button>
                <button @click="deleteEvent" class="dropdown-item delete">Delete</button>
              </div>
            </div>
          </div>

          <div v-if="selectedEvents.length">
            <h4>Events:</h4>
            <ul>
              <li v-for="(event, i) in selectedEvents" :key="i">
                <strong>{{ event.type }}</strong>: {{ event.title }}
              </li>
            </ul>
          </div>

          <div v-else>
            <p>No events scheduled for this day.</p>
          </div>

          <button @click="closeModal" class="calendar-modal-close-btn">Close</button>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import dictLogo from "@/assets/images/DICT-Logo-icon_only (1).png";
import axios from "axios";

export default {
  name: "OrganizationCalendar",
  data() {
    return {
      dictLogo,
      currentDate: new Date(),
      dayNames: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],

      // 👇 added for modal functionality
      showModal: false,
      selectedDate: null,
      selectedEvents: [],
      showMenu: false,

      // Hardcoded upcoming trainings
      trainings: [],

      // Hardcoded on-going careers
      careers: []
    }
  },
  computed: {
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

    // 👇 ADD THIS NEW COMPUTED
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
    async fetchTrainings() {
      const token = localStorage.getItem("token");
      if (!token) return;

      const res = await axios.get("http://127.0.0.1:8000/api/trainings", {
        headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
      });

      this.trainings = res.data.map(t => ({
        id: t.trainingID,
        title: t.title,
        description: t.description,
        date: t.schedule.split(" ")[0], 
        mode: t.mode,
        location: t.location,
        link: t.trainingLink,
        organizationID: t.organization.organizationID,
      }));
    },

    async fetchCareers() {
      const token = localStorage.getItem("token");
      if (!token) return;

      const res = await axios.get("http://127.0.0.1:8000/api/careers", {
        headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
      });

      this.careers = res.data.map(c => ({
        id: c.careerID,
        title: c.position,
        details: c.detailsAndInstruction,
        qualifications: c.qualifications,
        requirements: c.requirements,
        letterAddress: c.applicationLetterAddress,
        date: c.deadlineOfSubmission.split(" ")[0], 
        organizationID: c.organization.organizationID,
      }));
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
      return (
        this.trainings.some(t => t.date === this.formatDate(date)) ||
        this.careers.some(c => c.date === this.formatDate(date))
      );
    },
    getEventTitles(date) {
      if (!date) return [];
      const formatted = this.formatDate(date);
      const trainingTitles = this.trainings
        .filter(t => t.date === formatted)
        .map(t => "🟦 " + t.title);
      const careerTitles = this.careers
        .filter(c => c.date === formatted)
        .map(c => "🟨 " + c.title);
      return [...trainingTitles, ...careerTitles];
    },
    getEventColor(date) {
      if (!date) return "";
      const formatted = this.formatDate(date);
      if (this.trainings.some(t => t.date === formatted)) return "#d0e6ff"; // light blue
      if (this.careers.some(c => c.date === formatted)) return "#fff5c2"; // light yellow
      return "";
    },
    formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },

    // 👇 ADD THESE NEW METHODS
    openDayModal(date) {
      if (!date) return;
      this.selectedDate = date;

      const formatted = this.formatDate(date);
      const trainings = this.trainings
        .filter(t => t.date === formatted)
        .map(t => ({ type: "Training", title: t.title }));

      const careers = this.careers
        .filter(c => c.date === formatted)
        .map(c => ({ type: "Career", title: c.title }));

      this.selectedEvents = [...trainings, ...careers];
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.selectedDate = null;
      this.selectedEvents = [];
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
    this.fetchTrainings();
    this.fetchCareers();
  },
};
</script>


<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from 'vue-router';

const router = useRouter();
const isSidebarOpen = ref(true);
const organizationName = ref("");

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

onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    try {
      const user = JSON.parse(storedUser);
      if (user.role === "organization") {
        organizationName.value = user.displayName || user.name || "Organization";
      }
    } catch (e) {
      console.error("Failed to parse user from localStorage:", e);
    }
  }
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
  max-width: 700px;
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

/* Calendar */
.calendar-section {
  background: #fff;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
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

/* === Calendar Modal Styles === */
.calendar-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  animation: fadeIn 0.2s ease-in-out;
}

.calendar-modal-content {
  background: #ffffff;
  padding: 24px 28px;
  border-radius: 14px;
  max-width: 420px;
  width: 90%;
  box-shadow: 0 5px 18px rgba(0, 0, 0, 0.25);
  text-align: center;
  animation: zoomIn 0.25s ease;
}

.calendar-modal-content h3 {
  font-size: 1.2rem;
  margin-bottom: 10px;
  color: #1e293b;
  /* slate-800 */
}

.calendar-modal-content h4 {
  margin-top: 15px;
  font-weight: 600;
  color: #44576D;
  /* blue-600 */
}

.calendar-modal-content ul {
  text-align: left;
  margin: 10px 0 15px;
  padding-left: 10px;
}

.calendar-modal-content li {
  background: #f8fafc;
  padding: 6px 8px;
  border-radius: 6px;
  margin-bottom: 6px;
  font-size: 0.95rem;
}

.calendar-modal-close-btn {
  background: #44576D;
  color: #fff;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.calendar-modal-close-btn:hover {
  background: #374151;
}

/* === Animations === */
@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes zoomIn {
  from {
    transform: scale(0.9);
    opacity: 0.6;
  }

  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* === Calendar Modal Header + 3-dot menu === */
.calendar-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  position: relative;
}

.calendar-modal-menu {
  position: relative;
  margin-top: -25px;
}

.calendar-modal-menu-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  color: #1e293b;
  padding: 4px;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.calendar-modal-menu-btn:hover {
  background: #f1f5f9;
}

.calendar-modal-dropdown {
  position: absolute;
  top: 28px;
  right: 0;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  width: 120px;
  z-index: 10;
  overflow: hidden;
  animation: fadeIn 0.15s ease-in-out;
}

.calendar-modal-dropdown .dropdown-item {
  width: 100%;
  text-align: left;
  padding: 8px 12px;
  border: none;
  background: none;
  font-size: 0.95rem;
  cursor: pointer;
  color: #1e293b;
  transition: background 0.2s ease;
}

.calendar-modal-dropdown .dropdown-item:hover {
  background: #f1f5f9;
}

.calendar-modal-dropdown .dropdown-item.delete {
  color: #dc2626;
  /* red */
}
</style>