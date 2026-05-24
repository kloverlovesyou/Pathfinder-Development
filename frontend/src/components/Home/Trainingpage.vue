<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { useRegistrationStore } from "@/stores/registrationStore";
import { useTrainingStore } from "@/stores/trainingStore";
import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

const trainingStore = useTrainingStore();
const regStore = useRegistrationStore();
const organizations = ref([]);
const selectedTraining = ref(null);
const toasts = ref([]);
const loadingTrainings = ref(true);

// ✅ Only include trainings whose schedule is not past
const upcomingTrainings = computed(() => {
  const now = new Date();

  return trainingStore.trainings.filter(t => {
    if (t.schedules?.length) {
      // If ANY schedule is upcoming → include training
      return t.schedules.some(s => {
        try {
          return new Date(s.end_time) >= now;
        } catch {
          return true; // in case of invalid date
        }
      });
    }

    // Fallback for trainings without schedules
    if (t.end_time) {
      try {
        return new Date(t.end_time) >= now;
      } catch {
        return true;
      }
    }

    // If no date info, assume upcoming
    return true;
  });
});

// Replace original "trainings" usage with this:
const trainingsToShow = upcomingTrainings;

const trainingsWithOrg = computed(() =>
  trainingStore.trainings.map((t) => {
    // Get organization name from the training object or organizations array
    const orgName = t.organization?.name || 
      organizations.value.find((o) => o.organizationID === t.organizationID)?.name ||
      "Unknown";
    return {
      ...t,
      organizationName: orgName,
      organization: t.organization || { name: orgName },
    };
  })
);

// ============================
// 📌 Modal Controls
// ============================
async function openTrainingModal(training) {
  selectedTraining.value = training;
  showModal.value = true;
}

function isTraining(post) {
  return post && (post.type === "training" || post.trainingID);
}

// ============================
// 🔔 Toast Function
// ============================
function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, 3000);
}

// ---------------------------
// Fetch APIs
// ---------------------------
async function fetchTrainings() {
  try {
    const token = localStorage.getItem("token");
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/trainings",
      {
        headers: token ? { Authorization: `Bearer ${token}` } : {},
      }
    );
    trainings.value = res.data;
  } catch (err) {
    console.error("❌ fetchTrainings error:", err.response?.data || err);
    addToast("Failed to load trainings", "error");
  }
}

async function fetchOrganizations() {
  try {
    const token = localStorage.getItem("token");
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/organizations",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    organizations.value = res.data;
  } catch {}
}

const trainings = computed(() => trainingStore.trainings);
function handleTrainingRegisterEvent(payload) {
  if (payload?.error) {
    console.error("Registration action failed:", payload.error);
    addToast("Failed to update registration", "error");
    return;
  }

  const registered = payload?.isRegistered;
  addToast(
    registered ? "Registered successfully!" : "Unregistered successfully!",
    registered ? "success" : "info"
  );
}
// ---------------------------
// Lifecycle
// ---------------------------
onMounted(async () => {
  loadingTrainings.value = true;

  await trainingStore.fetchTrainings();
  console.log("Trainings loaded:", trainingStore.trainings);
  console.log("Upcoming trainings:", upcomingTrainings.value);
  await fetchOrganizations();
  await regStore.fetchMyRegistrations();

    loadingTrainings.value = false;
});

const calendarOpen = ref(false);
function openModalCalendar(event) {
  console.log("Event clicked:", event);
}

import TrainingModal from "@/components/Layout/TrainingModal.vue";
const showModal = ref(false);
</script>

<template>
  <main class="font-poppins">
    <!-- Header -->

    <div class="bg-white m-3 p-4 rounded-lg">
      <div class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm">
        <h2 class="text-2xl font-bold mb-3 sticky top-0 bg-white z-10">
          Training
        </h2>
      </div>
      <!-- Training Cards -->
      <div class="space-y-4">

        <!-- ⏳ Loading State -->
        <div v-if="loadingTrainings" class="flex flex-col items-center justify-center py-10 space-y-3">
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
          <p class="text-gray-600">Loading trainings...</p>
        </div>

        <!-- ✅ Trainings loaded -->
        <div v-else-if="trainingsToShow.length > 0">
          <div
            v-for="training in trainingsToShow"
            :key="training.trainingID"
            class="p-4 mb-2 bg-blue-gray rounded-lg hover:bg-gray-300 transition cursor-pointer flex justify-between items-center"
            @click="openTrainingModal(training)"
          >
            <div>
              <h3 class="font-semibold text-sm">{{ training.title }}</h3>
              <p class="text-gray-700 text-xs">
                {{ training.organization?.name || training.organizationName || 'Unknown' }}
              </p>
            </div>
          </div>
        </div>

        <!-- 🚫 No trainings -->
        <div v-else class="p-6 text-center text-gray-600 bg-gray-100 rounded-lg">
          No available trainings at the moment.
        </div>

      </div>
    </div>

    <CalendarSidebar
      :isOpen="calendarOpen"
      @open="calendarOpen = true"
      @close="calendarOpen = false"
      @eventClick="openModalCalendar"
    />

    <TrainingModal
      :isOpen="showModal"
      :training="selectedTraining"
      @close="showModal = false"
      @toggle-register="handleTrainingRegisterEvent"
    />
    <!-- Toast Notifications -->
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
