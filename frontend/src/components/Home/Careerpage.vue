<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import axios from "axios";
import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

const calendarOpen = ref(false);

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

onMounted(async () => {
  try {
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/careers"
    );
    careers.value = response.data;
  } catch (error) {
    console.error("Error fetching careers:", error);
  }

  await fetchMyApplications();
});

// Merge careers with organization name
const careersWithOrg = computed(() =>
  careers.value.map((career) => ({
    ...career,
    organizationName: career.organizationName || "Unknown",
  }))
);

// --- Calendar + Events ---
const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);

function buildEvents() {
  events.value = {};
  careers.value.forEach((career) => {
    const date = career.closingDate ? career.closingDate.split("T")[0] : null;
    if (date) {
      if (!events.value[date]) events.value[date] = [];
      events.value[date].push(career);
    }
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
  
  if (!calendar) {
    console.warn('Calendar ref is null, skipping calendar setup');
    return;
  }
  
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
// ✅ Careers that have NOT passed their deadline
const upcomingCareers = computed(() => {
  const today = new Date().setHours(0, 0, 0, 0);

  return careers.value.filter((career) => {
    if (!career.closingDate) return false;
    const deadline = new Date(career.closingDate).setHours(0, 0, 0, 0);
    return deadline >= today; // Not past
  });
});

const selectedCareer = ref(null);
const showModal = ref(false);

function openModal(career) {
  selectedCareer.value = career;
  showModal.value = true;
  console.log("Opening modal for career:", career);
}

function closeModal() {
  showModal.value = false;
  selectedCareer.value = null;
}

async function fetchMyApplications() {
  const token = localStorage.getItem("token");
  if (!token) return;
  const res = await axios.get(
    import.meta.env.VITE_API_BASE_URL + "/applications",
    {
      headers: { Authorization: `Bearer ${token}` },
    }
  );
  myApplications.value = new Set(res.data.map((a) => a.careerID));
}
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
        <div v-if="upcomingCareers.length > 0">
          <div
            v-for="career in upcomingCareers"
            :key="career.careerID"
            class="p-4 mb-2 bg-blue-gray rounded-lg hover:bg-gray-300 transition cursor-pointer"
            @click="openModal(career)"
          >
            <h3 class="font-semibold">{{ career.position }}</h3>
            <p class="text-gray-700">{{ career.organizationName || career.organization || 'Unknown' }}</p>
          </div>
        </div>

        <div
          v-else
          class="p-6 text-center text-gray-600 bg-gray-100 rounded-lg"
        >
          No available career postings at the moment.
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
      @close="closeModal"
      @update-applications="myApplications = $event"
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
