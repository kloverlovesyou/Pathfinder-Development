<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";

import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

const calendarOpen = ref(false);

function openModalCalendar(event) {
  // Handle modal opening for training/career
  console.log("Event clicked:", event);
}

// Modal & sidebar
const isModalOpen = ref(false);
const selectedOrg = ref({});
const isSidebarOpen = ref(false);
const orgModal = ref(null);

const toasts = ref([]);

function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
  }, 3000);
}
// Organizations
const organizations = ref([]);

function openModal(item) {
  console.log("openModal called with item:", item);

  let org = item;

  if (item.organizationID) {
    org = organizations.value.find((o) => o.id === item.organizationID);
  }

  if (!org) {
    console.warn("Organization not found for modal:", item);
    return;
  }

  selectedOrg.value = JSON.parse(JSON.stringify(org));

  nextTick(() => {
    if (orgModal.value) orgModal.value.showModal();
  });
}

function closeModal() {
  if (orgModal.value && orgModal.value.open) {
    orgModal.value.close();
  }
  selectedOrg.value = {};
}

// Calendar + events
const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);

// Modal posts
const selectedPost = ref(null);

// Build events map
function buildEvents() {
  events.value = {};

  organizations.value.forEach((org) => {
    // Add trainings
    org.trainings.forEach((training) => {
      const date = training.schedule?.split("T")[0]; // YYYY-MM-DD
      if (!events.value[date]) events.value[date] = [];
      events.value[date].push({
        ...training,
        type: "training",
        organizationID: org.id,
      });
    });

    // Add careers
    org.careers.forEach((career) => {
      const date = career.deadline?.split("T")[0]; // Assuming it's ISO string
      if (!events.value[date]) events.value[date] = [];
      events.value[date].push({
        ...career,
        type: "career",
        organizationID: org.id,
      });
    });
  });
}

// Show events on calendar click
function showEvents(dateStr) {
  selectedDate.value = dateStr;
  dayEvents.value = events.value[dateStr] || [];
}

// Get organization name by ID (for events)
function getOrgNameById(id) {
  const org = organizations.value.find((o) => o.id === id);
  return org ? org.name : "Unknown Organization";
}

function isTraining(event) {
  return event.type === "training";
}

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
function formatDate(d) {
  if (!d) return "";
  return new Date(d).toLocaleDateString("en-US", {
    dateStyle: "long",
  });
}

// Format ISO date-time to readable string
function formatSchedule(dt) {
  if (!dt) return "N/A";
  const date = new Date(dt);
  return date.toLocaleString("en-US", {
    weekday: "short", // e.g., "Mon"
    month: "short", // e.g., "Oct"
    day: "numeric", // e.g., 31
    year: "numeric", // e.g., 2025
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}

// Fetch organizations from backend
onMounted(async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/organization");
    // Map backend fields to frontend template

    // Map backend fields for frontend usage
    organizations.value = res.data.map((org) => ({
      id: org.organizationID, // ← use organizationID
      name: org.name,
      location: org.location || "N/A",
      website: org.websiteURL || "#",
      logo: org.logo || null,
      careers: (org.careers || []).map((c) => ({
        id: c.careerID, // ← careerID from backend
        position: c.position,
        deadlineOfSubmission: c.deadlineOfSubmission,
        detailsAndInstructions: c.detailsAndInstructions,
        qualifications: c.qualifications,
        requirements: c.requirements,
        applicationLetterAddress: c.applicationLetterAddress,
      })),
      trainings: (org.trainings || []).map((t) => ({
        id: t.trainingID, // ← trainingID from backend
        title: t.title,
        description: t.description,
        schedule: t.schedule,
        end_time: t.end_time,
        mode: t.mode,
        location: t.location,
        trainingLink: t.trainingLink,
        attendance_key: t.attendance_key,
        attendance_expires_at: t.attendance_expires_at,
        qr_generated_at: t.qr_generated_at,
      })),
    }));
    buildEvents();
    await fetchBookmarks();

    // 3️⃣ Fetch user’s bookmarked careers
    await fetchCareerBookmarks();

    // 4️⃣ Fetch user’s applications
    await fetchMyApplications();
  } catch (error) {
    console.error("Error fetching organizations:", error);
  }

  await nextTick();
  console.log("orgModal after onMounted + nextTick:", orgModal.value);
  // Calendar events setup (if you have events)
  const calendar = calendarRef.value;
  const today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD

  if (calendar) {
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

    // Auto-select today's date
    calendar.value = today;
    showEvents(today);

    const event = new Event("change", { bubbles: true });
    calendar.dispatchEvent(event);
  }
});

import TrainingModal from "@/components/Layout/TrainingModal.vue";

const showModal = ref(false);
const selectedTraining = ref(null);
const bookmarkedTrainings = ref([]);
const myRegistrations = ref(new Set());

async function toggleRegister(training) {
  const token = localStorage.getItem("token");
  if (!token) {
    addToast("Please log in first", "accent");
    return;
  }

  // ✅ Optimistic update for instant feedback
  const wasRegistered = myRegistrations.value.has(training.trainingID);

  if (wasRegistered) {
    myRegistrations.value.delete(training.trainingID);
    try {
      await axios.delete(
        `http://127.0.0.1:8000/api/registrations/${training.trainingID}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      addToast("Unregistered successfully", "info");
    } catch (err) {
      myRegistrations.value.add(training.trainingID); // rollback
      addToast("Failed to unregister", "error");
    }
  } else {
    myRegistrations.value.add(training.trainingID);
    try {
      await axios.post(
        "http://127.0.0.1:8000/api/registrations",
        { trainingID: training.trainingID },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      addToast("Registered successfully", "success");
    } catch (err) {
      myRegistrations.value.delete(training.trainingID); // rollback
      addToast("Failed to register", "error");
    }
  }
}

function isTrainingBookmarked(trainingID) {
  return bookmarkedTrainings.value.includes(trainingID);
}

// Toggle bookmark for a training
async function toggleTrainingBookmark(trainingID) {
  if (!trainingID) return;

  const token = localStorage.getItem("token");
  if (!token) {
    addToast("PLEASE LOG IN FIRST", "accent");
    return;
  }

  const isBookmarked = bookmarkedTrainings.value.includes(trainingID);

  try {
    if (isBookmarked) {
      // Remove bookmark
      await axios.delete(`http://127.0.0.1:8000/api/bookmarks/${trainingID}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      bookmarkedTrainings.value = bookmarkedTrainings.value.filter(
        (id) => id !== trainingID
      );
      addToast("Bookmark removed", "info");
    } else {
      // Add bookmark
      await axios.post(
        "http://127.0.0.1:8000/api/bookmarks",
        { trainingID },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      bookmarkedTrainings.value.push(trainingID);
      addToast("Bookmarked!", "success");
    }
  } catch (error) {
    if (error.response?.status === 409) {
      // Already bookmarked — update local state to match backend
      if (!bookmarkedTrainings.value.includes(trainingID)) {
        bookmarkedTrainings.value.push(trainingID);
      }
      addToast("Already bookmarked", "accent");
    } else {
      console.error("Failed to toggle bookmark:", error.response || error);
      addToast("Failed to toggle bookmark", "error");
    }
  }
}

async function openTrainingModal(training) {
  console.log("Opening training modal:", training);
  if (bookmarkedTrainings.value.length === 0) {
    await fetchBookmarks();
  }
  // Set the selected training
  selectedTraining.value = training;

  // ✅ Close the org modal first (important!)
  if (orgModal.value && orgModal.value.open) {
    orgModal.value.close();
  }

  // ✅ Then open the training modal
  nextTick(() => {
    showModal.value = true;
  });
}

import CareerModal from "@/components/Layout/CareerModal.vue";

const selectedCareer = ref(null);
const showCareerModal = ref(false);
const myApplications = ref(new Set());
const bookmarkedCareers = ref(new Set());
async function fetchCareerBookmarks() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get("http://127.0.0.1:8000/api/career-bookmarks", {
      headers: { Authorization: `Bearer ${token}` },
    });

    bookmarkedCareers.value = new Set(res.data.map((b) => b.careerID));
  } catch (error) {
    console.error("Error fetching career bookmarks:", error);
  }
}

async function fetchMyApplications() {
  const token = localStorage.getItem("token");
  if (!token) return;

  try {
    const res = await axios.get("http://127.0.0.1:8000/api/applications", {
      headers: { Authorization: `Bearer ${token}` },
    });

    myApplications.value = new Set(res.data.map((a) => a.careerID));
  } catch (error) {
    console.error("Error fetching applications:", error);
  }
}

// Open modal function
function openCareerModal(career) {
  selectedCareer.value = career;
  showCareerModal.value = true;
  console.log("Opening modal for:", career);
  console.log("Show state before:", showCareerModal.value);
  if (orgModal.value && orgModal.value.open) {
    orgModal.value.close();
  }
}

// Close modal function
function closeCareerModal() {
  showCareerModal.value = false;
  selectedCareer.value = null;
}
</script>

<template>
  <main class="font-poppins">
    <div class="bg-white m-3 p-4 rounded-lg">
      <!-- Header -->
      <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm">
        <h2 class="text-2xl font-bold mb-3">Organizations</h2>
      </div>

      <!-- Organization List -->
      <div class="space-y-4">
        <div
          v-for="org in organizations"
          :key="org.id"
          class="flex items-center gap-4 p-4 bg-blue-gray rounded-lg hover:bg-gray-200 transition cursor-pointer"
          @click="openModal(org)"
        >
          <!-- Profile Picture / Logo -->
          <div v-if="selectedOrg && selectedOrg.id" class="flex-shrink-0">
            <img
              v-if="org.logo"
              :src="org.logo"
              alt="Organization Logo"
              class="rounded-full w-16 h-16 object-cover"
            />
            <div
              v-else
              class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-sm"
            >
              No Logo
            </div>
          </div>

          <!-- Org Name -->
          <div>
            <p class="text-base font-semibold text-gray-800">
              {{ org.name }}
            </p>
            <p class="text-sm text-gray-600">{{ org.location }}</p>
          </div>
        </div>
      </div>
    </div>

    <CalendarSidebar
      :isOpen="calendarOpen"
      @open="calendarOpen = true"
      @close="calendarOpen = false"
      @eventClick="openModalCalendar"
    />

    <!-- Organization Details Modal -->
    <dialog ref="orgModal" class="modal sm:modal-middle">
      <div class="modal-box max-w-2xl relative font-poppins">
        <!-- Close button -->
        <button
          @click="closeModal"
          class="btn btn-sm btn-circle absolute right-2 top-2"
        >
          ✕
        </button>

        <!-- Organization Info -->
        <div
          v-if="selectedOrg && selectedOrg.id"
          class="flex items-center gap-3 mb-3"
        >
          <img
            v-if="(selectedOrg && selectedOrg.logo) || (org && org.logo)"
            :src="selectedOrg?.logo || org?.logo"
            alt="Organization Logo"
            class="w-16 h-16 rounded-full object-cover"
          />

          <div
            v-else
            class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-sm"
          >
            No Logo
          </div>
          <div>
            <h2 class="text-xl font-bold">
              {{ selectedOrg.name || "No Name" }}
            </h2>
            <p class="text-sm text-gray-600">
              {{ selectedOrg.location || "No location available" }}
            </p>
          </div>
        </div>

        <!-- Website -->
        <p v-if="selectedOrg && selectedOrg.id" class="text-sm mb-5">
          <a
            :href="selectedOrg.website"
            target="_blank"
            class="text-blue-600 underline"
          >
            {{ selectedOrg.website }}
          </a>
        </p>

        <!-- Career Posts -->
        <div class="mt-4">
          <h3 class="text-base font-semibold mb-3">Career Posts</h3>
          <div class="flex overflow-x-auto space-x-3 pb-2 snap-x">
            <div
              v-for="career in selectedOrg.careers"
              :key="career.id"
              class="snap-start w-[180px] p-3 bg-gray-100 rounded-lg shadow-sm"
              @click="openCareerModal(career)"
            >
              <h4 class="font-semibold text-sm">{{ career.position }}</h4>
              <p class="text-[11px] text-gray-600">
                Deadline: {{ formatDateTime(career.deadlineOfSubmission) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Training Posts -->
        <div class="mt-6">
          <h3 class="text-base font-semibold mb-3">Training Posts</h3>
          <div class="flex overflow-x-auto space-x-3 pb-2 snap-x">
            <div
              v-for="training in selectedOrg.trainings"
              :key="training.id"
              class="snap-start w-[180px] p-3 bg-gray-100 rounded-lg shadow-sm cursor-pointer"
              @click="openTrainingModal(training)"
            >
              <h4 class="font-semibold text-sm">{{ training.title }}</h4>
              <p class="text-[11px] text-gray-600">
                Schedule: {{ formatSchedule(training.schedule) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </dialog>
    <TrainingModal
      v-if="showModal"
      :isOpen="showModal"
      :training="selectedTraining"
      :isRegistered="myRegistrations.has(selectedTraining?.id) || false"
      :isBookmarked="bookmarkedTrainings.includes(selectedTraining?.id)"
      @toggle-register="toggleRegister"
      @bookmark="(trainingID) => toggleTrainingBookmark(selectedTraining?.id)"
      @close="showModal = false"
    />

    <CareerModal
      v-if="showCareerModal"
      :show="showCareerModal"
      :career="selectedCareer"
      :myApplications="myApplications"
      :bookmarkedCareers="bookmarkedCareers"
      @close="closeCareerModal"
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

<style scoped>
.modal {
  border: none;
  border-radius: 0.5rem;
  padding: 0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.modal::backdrop {
  background: rgba(0, 0, 0, 0.4);
}
</style>
