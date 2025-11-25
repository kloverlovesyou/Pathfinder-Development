<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { useRegistrationStore } from "@/stores/registrationStore";
import { useTrainingStore } from "@/stores/trainingStore";
import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

const trainingStore = useTrainingStore();
const regStore = useRegistrationStore();
const myRegistrations = computed(() => regStore.myRegistrations);
const organizations = ref([]);
const selectedTraining = ref(null);
const toasts = ref([]);

// ✅ Only include trainings whose schedule is not past
const upcomingTrainings = computed(() => {
  const today = new Date().setHours(0, 0, 0, 0);

  return trainingStore.trainings.filter((t) => {
    const end = new Date(t.endDate || t.date).setHours(0, 0, 0, 0);
    return end >= today;
  });
});

// Replace original "trainings" usage with this:
const trainingsToShow = upcomingTrainings;

const trainingsWithOrg = computed(() =>
  trainingStore.trainings.map((t) => {
    const org = organizations.value.find(
      (o) => o.organizationID === t.organizationID
    );
    return {
      ...t,
      organizationName: org ? org.name : "Unknown",
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
      import.meta.env.VITE_API_BASE_URL + "/organization",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    organizations.value = res.data;
  } catch {}
}

// ---------------------------
// Toggle Registration using store
// ---------------------------
async function toggleRegister(training) {
  await regStore.toggleRegister(training.trainingID);
}
const trainings = computed(() => trainingStore.trainings);
// ---------------------------
// Lifecycle
// ---------------------------
onMounted(async () => {
  await trainingStore.fetchTrainings();
  await fetchOrganizations();
  await regStore.fetchMyRegistrations();
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
        <div v-if="trainingsToShow.length > 0">
          <div
            v-for="training in trainingsToShow"
            :key="training.trainingID"
            class="p-4 mb-2 bg-blue-gray rounded-lg hover:bg-gray-300 transition cursor-pointer flex justify-between items-center"
            @click="openTrainingModal(training)"
          >
            <!-- Left: Training info -->
            <div>
              <h3 class="font-semibold">{{ training.title }}</h3>
              <p class="text-gray-700">
                {{ training.organization.name }}
              </p>
            </div>
          </div>
        </div>

        <!-- 🚫 Nothing available -->
        <div
          v-else
          class="p-6 text-center text-gray-600 bg-gray-100 rounded-lg"
        >
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
      :isRegistered="myRegistrations.has(selectedTraining?.trainingID)"
      :registerLoading="regStore.loading[selectedTraining?.trainingID]"
      @close="showModal = false"
      @toggle-register="toggleRegister"
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
