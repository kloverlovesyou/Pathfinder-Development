<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import axios from "axios";

import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

const calendarOpen = ref(false);
const careerBookmarkLoading = reactive({});

function openModalCalendar(event) {
  // Handle modal opening for training/career
  console.log("Event clicked:", event);
}

const toasts = ref([]);
const myApplications = ref(new Set());

function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, 3000);
}

// ✅ Fetch user's applications

// ✅ Fetch user's bookmarked careers
const bookmarkedCareers = ref(new Set());

async function fetchCareerBookmarks() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL +"/career-bookmarks", {
      headers: { Authorization: `Bearer ${token}` },
    });
    bookmarkedCareers.value = new Set(res.data.map((b) => b.careerID));
  } catch (error) {
    console.error("Error fetching career bookmarks:", error);
  }
}

// ✅ Check if career is bookmarked
function isCareerBookmarked(careerId) {
  return bookmarkedCareers.value.has(careerId);
}

// ✅ Toggle bookmark with backend sync
  async function toggleCareerBookmark(careerId) {
    const token = localStorage.getItem("token");
    if (!token) {
      addToast("PLEASE LOG IN FIRST", "accent");
      return;
    }

    careerBookmarkLoading[careerId] = true; // ✅ start loading

    try {
      if (bookmarkedCareers.value.has(careerId)) {
        await axios.delete(
          import.meta.env.VITE_API_BASE_URL + `/career-bookmarks/${careerId}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        bookmarkedCareers.value.delete(careerId);
        addToast("Bookmark removed", "success");
      } else {
        await axios.post(
          import.meta.env.VITE_API_BASE_URL + "/career-bookmarks",
          { careerID: careerId },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        bookmarkedCareers.value.add(careerId);
        addToast("Bookmark added", "success");
      }
    } catch (error) {
      console.error("Error toggling career bookmark:", error);
      addToast("Failed to toggle bookmark", "error");
    } finally {
      careerBookmarkLoading[careerId] = false; // ✅ stop loading
    }
  }

// ✅ Fetch careers
onMounted(async () => {
  try {
    const response = await axios.get(import.meta.env.VITE_API_BASE_URL +"/careers");
    careers.value = response.data;
  } catch (error) {
    console.error("Error fetching careers:", error);
  }

  await fetchMyApplications();
  await fetchCareerBookmarks(); // <-- Fetch bookmarks after login
});

// Merge careers with organization name
const careersWithOrg = computed(() =>
  careers.value.map((career) => ({
    ...career,
    organizationName: career.organizationName || "Unknown",
  }))
);

const showUploadModal = ref(false);

const uploadedFile = ref(null);

function openUploadModal() {
  showUploadModal.value = true;
}

function closeUploadModal() {
  showUploadModal.value = false;
  uploadedFile.value = null;
}

function handleFileUpload(event) {
  uploadedFile.value = event.target.files[0];
}

async function submitApplication() {
  if (!selectedCareer.value) return;

  try {
    const token = localStorage.getItem("token");
    if (!token) {
      addToast("PLEASE LOG IN FIRST", "accent");
      return;
    }

    const form = new FormData();
    form.append(
      "careerID",
      selectedCareer.value.careerID ?? selectedCareer.value.id
    );
    if (uploadedFile.value) {
      form.append("requirements", uploadedFile.value);
    }

    await axios.post(import.meta.env.VITE_API_BASE_URL + "/applications", form, {
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "multipart/form-data",
      },
    });

    addToast("APPLICATION SUBMITTED SUCCESSFULLY", "success");
    const id = selectedCareer.value.careerID ?? selectedCareer.value.id;
    myApplications.value.add(id);
    closeUploadModal();
    closeModal();
  } catch (error) {
    if (error.response?.status === 409) {
      addToast("YOU ALREADY APPLIED TO THIS CAREER", "accent");
    } else if (error.response?.status === 401) {
      addToast("UNAUTHORIZED. PLEASE LOG IN AGAIN", "accent");
    } else if (error.response?.status === 422) {
      addToast("INVALID INPUT. ONLY PDF UP TO 5MB", "accent");
    } else {
      addToast("FAILED TO SUBMIT APPLICATION", "accent");
    }
  }
}

// --- Calendar + Events ---
const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);

function buildEvents() {
  events.value = {};
  posts.value.forEach((post) => {
    const date = post.trainingID
      ? post.schedule.split("T")[0]
      : post.deadlineOfSubmission;
    if (!events.value[date]) events.value[date] = [];
    events.value[date].push(post);
  });
}

function showEvents(dateStr) {
  selectedDate.value = dateStr;
  dayEvents.value = events.value[dateStr] || [];
}

onMounted(async () => {
  await nextTick();
  buildEvents();
  const calendar = calendarRef.value;
  const today = new Date().toISOString().split("T")[0];

  calendar.addEventListener("render", () => {
    calendar.querySelectorAll("[data-date]").forEach((el) => {
      const dateStr = el.getAttribute("data-date");
      el.classList.remove("event-day", "today");
      if (events.value[dateStr]) el.classList.add("event-day");
      if (dateStr === today) el.classList.add("today");
    });
  });

  calendar.addEventListener("change", (e) => {
    const pickedDate = e.target.value;
    showEvents(pickedDate);
    calendar
      .querySelectorAll("[data-date]")
      .forEach((el) => el.classList.remove("selected-day"));
    const selectedEl = calendar.querySelector(`[data-date="${pickedDate}"]`);
    if (selectedEl) selectedEl.classList.add("selected-day");
  });

  calendar.value = today;
  showEvents(today);
  const event = new Event("change", { bubbles: true });
  calendar.dispatchEvent(event);
});

// ✅ Format datetime (with AM/PM)
function formatDateTime(dateStr) {
  if (!dateStr) return "N/A";
  const date = new Date(dateStr);
  if (isNaN(date)) return dateStr;

  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}

import CareerModal from "@/components/Layout/CareerModal.vue";

const careers = ref([]);
const selectedCareer = ref(null);
const showModal = ref(false);

function openModal(career) {
  selectedCareer.value = career;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedCareer.value = null;
}

async function fetchMyApplications() {
  const token = localStorage.getItem("token");
  if (!token) return;
  const res = await axios.get(import.meta.env.VITE_API_BASE_URL +"/applications", {
    headers: { Authorization: `Bearer ${token}` },
  });
  myApplications.value = new Set(res.data.map((a) => a.careerID));
}

async function fetchBookmarks() {
  const token = localStorage.getItem("token");
  if (!token) return;
  const res = await axios.get(import.meta.env.VITE_API_BASE_URL +"/career-bookmarks", {
    headers: { Authorization: `Bearer ${token}` },
  });
  bookmarkedCareers.value = new Set(res.data.map((b) => b.careerID));
}

onMounted(async () => {
  await fetchCareers();
  await fetchMyApplications();
  await fetchBookmarks();
});
</script>

<template>
  <main class="font-poppins">
    <!-- Header -->

    <div class="bg-white m-3 p-4 rounded-lg">
      <!-- Sticky Header -->
      <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm">
        <h2 class="text-2xl font-bold mb-3 sticky top-0 bg-white z-10">
          Career
        </h2>
      </div>
      <!-- Career Cards -->
      <div class="space-y-4">
        <div
          v-for="career in careers"
          :key="career.careerID"
          class="p-4 bg-blue-gray rounded-lg relative hover:bg-gray-300 transition cursor-pointer"
          @click="openModal(career)"
        >
          <h3 class="font-semibold text-lg">{{ career.position }}</h3>
          <p class="text-gray-700 font-medium">
            {{ career.organizationName }}
          </p>
        </div>
      </div>
    </div>

    <CalendarSidebar
      :isOpen="calendarOpen"
      @open="calendarOpen = true"
      @close="calendarOpen = false"
      @eventClick="openModalCalendar"
    />

    <CareerModal
      :show="showModal"
      :career="selectedCareer"
      :myApplications="myApplications"
      :bookmarkedCareers="bookmarkedCareers"
      :careerBookmarkLoading="careerBookmarkLoading[selectedCareer?.careerID]"
      @close="closeModal"
      @update-applications="myApplications = $event"
      @update-bookmarks="bookmarkedCareers = $event"
    />
    <div class="toast toast-end toast-top z-50">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="alert"
        :class="{
          'alert-info': toast.type === 'info',
          'alert-success': toast.type === 'success',
          'alert-accent': toast.type === 'accent',
        }"
      >
        {{ toast.message }}
      </div>
    </div>
  </main>
</template>
