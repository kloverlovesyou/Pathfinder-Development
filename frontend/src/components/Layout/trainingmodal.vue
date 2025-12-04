<script setup>
import { computed, ref } from "vue";
import { useRegistrationStore } from "@/stores/registrationStore";
import axios from "axios";

const props = defineProps({
  isOpen: Boolean,
  training: Object,
});

const emit = defineEmits(["close", "toggle-register"]);
const regStore = useRegistrationStore();
const actionError = ref("");
const showConflictDialog = ref(false);
const conflictingTrainings = ref([]);

const trainingId = computed(() => resolveTrainingId(props.training));
const isRegistered = computed(() => {
  const id = trainingId.value;
  return id ? !!regStore.registeredPosts[id] : false;
});
const isLoading = computed(() => {
  const id = trainingId.value;
  return id ? !!regStore.loading[id] : false;
});
const canToggleRegistration = computed(() => !!trainingId.value && !isLoading.value);

// Check for same-day onsite conflicts
async function checkSameDayOnsiteConflict(newTraining) {
  const token = localStorage.getItem("token");
  if (!token) return false;

  try {
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      { headers: { Authorization: `Bearer ${token}` } }
    );

    const existingRegistrations = res.data || [];
    const newTrainingSchedules = newTraining?.schedules || [];
    
    if (!Array.isArray(newTrainingSchedules) && newTraining?.schedule) {
      newTrainingSchedules.push({
        schedule: newTraining.schedule,
        mode: newTraining.mode,
        location: newTraining.location,
      });
    }

    const newTrainingHasOnsite = newTrainingSchedules.some((schedule) => {
      const mode = (schedule?.mode || "").toString().toLowerCase();
      return mode === "on-site" || mode === "onsite";
    });

    if (!newTrainingHasOnsite) return false;

    const newTrainingDates = new Set();
    newTrainingSchedules.forEach((schedule) => {
      const scheduleDate = schedule?.schedule || schedule?.start_time;
      if (scheduleDate) {
        const date = new Date(scheduleDate);
        const dateStr = date.toISOString().split("T")[0];
        newTrainingDates.add(dateStr);
      }
    });

    const conflicts = [];
    existingRegistrations.forEach((registration) => {
      const existingTraining = registration?.training;
      if (!existingTraining) return;

      const existingSchedules = existingTraining?.schedules || [];
      if (!Array.isArray(existingSchedules) && existingTraining?.schedule) {
        existingSchedules.push({
          schedule: existingTraining.schedule,
          mode: existingTraining.mode,
          location: existingTraining.location,
        });
      }

      existingSchedules.forEach((existingSchedule) => {
        const existingMode = (existingSchedule?.mode || "").toString().toLowerCase();
        if (existingMode !== "on-site" && existingMode !== "onsite") return;

        const existingScheduleDate = existingSchedule?.schedule || existingSchedule?.start_time;
        if (existingScheduleDate) {
          const existingDate = new Date(existingScheduleDate);
          const existingDateStr = existingDate.toISOString().split("T")[0];

          if (newTrainingDates.has(existingDateStr)) {
            conflicts.push({
              training: existingTraining,
              schedule: existingSchedule,
              date: existingDateStr,
            });
          }
        }
      });
    });

    return conflicts.length > 0 ? conflicts : false;
  } catch (error) {
    console.error("Error checking conflicts:", error);
    return false;
  }
}

async function handleRegisterClick() {
  if (!props.training || isLoading.value) return;

  const id = trainingId.value;
  if (!id) {
    actionError.value = "Unable to determine training ID.";
    emit("toggle-register", {
      training: props.training,
      error: new Error(actionError.value),
    });
    return;
  }

  // Only check conflicts when registering (not unregistering)
  if (!isRegistered.value) {
    const conflicts = await checkSameDayOnsiteConflict(props.training);
    if (conflicts && conflicts.length > 0) {
      conflictingTrainings.value = conflicts;
      showConflictDialog.value = true;
      return;
    }
  }

  actionError.value = "";

  try {
    await regStore.toggleRegister(props.training);

    emit("toggle-register", {
      training: props.training,
      isRegistered: isRegistered.value,
    });
  } catch (error) {
    console.error("Failed to toggle registration:", error);
    actionError.value = "Failed to update registration. Please try again.";
    emit("toggle-register", { training: props.training, error });
  }
}

async function confirmConflictRegistration() {
  showConflictDialog.value = false;
  conflictingTrainings.value = [];
  actionError.value = "";

  try {
    await regStore.toggleRegister(props.training);
    emit("toggle-register", {
      training: props.training,
      isRegistered: isRegistered.value,
    });
  } catch (error) {
    console.error("Failed to toggle registration:", error);
    actionError.value = "Failed to update registration. Please try again.";
    emit("toggle-register", { training: props.training, error });
  }
}

function cancelConflictRegistration() {
  showConflictDialog.value = false;
  conflictingTrainings.value = [];
}

function resolveTrainingId(training) {
  if (!training) return null;
  return (
    training.trainingID ??
    training.TrainingID ??
    training.id ??
    training.ID ??
    null
  );
}

function formatDate(datetime) {
  if (!datetime) return "N/A";
  return new Date(datetime).toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
}

function formatTime(datetime) {
  if (!datetime) return "N/A";
  return new Date(datetime).toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatScheduleFull(schedule) {
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
}

function formatScheduleTime(schedule) {
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
}
</script>

<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
    <div class="training-details-modal">
      <button class="modal-close-btn" @click="$emit('close')">✕</button>

      <template v-if="training">
        <div class="flex-1 overflow-y-auto pr-4">
          <!-- Training Title -->
          <h3 class="modal-title">
            {{ training.title || "Untitled Training" }}
          </h3>

          <!-- Organization -->
          <p class="training-info">
            <strong>Organization:</strong>
            {{
              training.organization?.name ||
              training.organizationName ||
              "Unknown"
            }}
          </p>

          <!-- Description -->
          <p class="training-info">
            <strong>Description:</strong> {{ training.description }}
          </p>
          <!-- Schedule/s Label -->
          <div class="training-info">
            <strong>Schedule/s:</strong>
          </div>

          <!-- Schedules Display (Card Style) -->
          <div class="schedules-container">
          <!-- Multiple Schedules -->
          <div
            v-if="training.schedules && training.schedules.length > 0"
            class="schedules-grid"
          >
            <div
              v-for="(schedule, index) in training.schedules"
              :key="schedule.trainingScheduleID || index"
              class="schedule-card"
            >
              <!-- Date and Time -->
              <div class="schedule-date-time">
                <svg
                  class="schedule-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
                <span
                  >{{ formatScheduleFull(schedule.schedule) }} -
                  {{ formatScheduleTime(schedule.end_time) }}</span
                >
              </div>

              <!-- Mode Badge -->
              <div
                class="schedule-mode-badge"
                :class="
                  schedule.mode === 'On-Site' ? 'mode-onsite' : 'mode-online'
                "
              >
                <svg
                  class="mode-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                <span>{{ schedule.mode }}</span>
              </div>

              <!-- Location (for On-Site) -->
              <div
                v-if="schedule.mode === 'On-Site' && schedule.location"
                class="schedule-location"
              >
                <svg
                  class="schedule-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                <span>{{ schedule.location }}</span>
              </div>

              <!-- Training Link (for Online) -->
              <div
                v-if="schedule.mode === 'Online' && schedule.trainingLink"
                class="schedule-link"
              >
                <svg
                  class="schedule-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                  />
                </svg>
                <a :href="schedule.trainingLink" target="_blank" class="training-link">
                  {{ schedule.trainingLink }}
                </a>
              </div>
            </div>
          </div>

          <!-- Single Schedule (Backward Compatibility) -->
          <div v-else-if="training.schedule" class="schedule-card">
            <!-- Date and Time -->
            <div class="schedule-date-time">
              <svg
                class="schedule-icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
              </svg>
              <span
                >{{ formatScheduleFull(training.schedule) }} -
                {{ formatScheduleTime(training.end_time) }}</span
              >
            </div>

            <!-- Mode Badge -->
            <div
              class="schedule-mode-badge"
              :class="training.mode === 'On-Site' ? 'mode-onsite' : 'mode-online'"
            >
              <svg
                class="mode-icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              <span>{{ training.mode || training.Mode }}</span>
            </div>

            <!-- Location (for On-Site) -->
            <div
              v-if="training.mode === 'On-Site' && training.location"
              class="schedule-location"
            >
              <svg
                class="schedule-icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              <span>{{ training.location }}</span>
            </div>

            <!-- Training Link (for Online) -->
            <div
              v-if="training.mode === 'Online' && training.trainingLink"
              class="schedule-link"
            >
              <svg
                class="schedule-icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                />
              </svg>
              <a :href="training.trainingLink" target="_blank" class="training-link">
                {{ training.trainingLink }}
              </a>
            </div>
          </div>

          <!-- No Schedule -->
          <div v-else class="schedule-card">
            <p class="text-gray-500">No schedule set</p>
          </div>
          </div>
        </div>
      </template>

      <!-- Buttons at bottom - full width -->
      <div class="mt-4 pt-4 border-t border-gray-200 flex flex-col gap-2">
        <button
          class="btn w-full text-white flex items-center justify-center gap-2"
          :class="isRegistered ? 'bg-gray-500' : 'bg-customButton'"
          @click="handleRegisterClick"
          :disabled="!canToggleRegistration"
        >
          <svg
            v-if="isLoading"
            class="animate-spin h-4 w-4 text-white"
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
              d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3h-4z"
            ></path>
          </svg>
          <span v-else>{{ isRegistered ? "Unregister" : "Register" }}</span>
        </button>
        <p v-if="actionError" class="text-xs text-red-500 text-center">
          {{ actionError }}
        </p>
      </div>
    </div>

    <!-- Conflict Confirmation Dialog -->
    <div v-if="showConflictDialog" class="modal-overlay" @click.self="cancelConflictRegistration">
      <div class="training-details-modal">
        <button class="modal-close-btn" @click="cancelConflictRegistration">✕</button>

        <h3 class="modal-title text-orange-600">
          ⚠️ Schedule Conflict Detected
        </h3>

        <div class="training-info">
          <p class="mb-3">
            You are trying to register for an <strong>onsite training</strong> that is scheduled on the same day as another onsite training you're already registered for.
          </p>

          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-3">
            <p class="font-semibold text-gray-800 mb-2">New Training:</p>
            <p class="text-gray-700">{{ training?.title || "Training" }}</p>
            <p v-if="training?.schedules?.[0]?.schedule" class="text-sm text-gray-600 mt-1">
              Date: {{ formatDate(training.schedules[0].schedule) }}
            </p>
          </div>

          <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="font-semibold text-gray-800 mb-2">Conflicting Training(s):</p>
            <div v-for="(conflict, index) in conflictingTrainings" :key="index" class="mb-2 last:mb-0">
              <p class="text-gray-700">{{ conflict.training?.title || "Training" }}</p>
              <p v-if="conflict.schedule?.schedule" class="text-sm text-gray-600">
                Date: {{ formatDate(conflict.schedule.schedule) }}
              </p>
              <p v-if="conflict.schedule?.location" class="text-sm text-gray-600">
                Location: {{ conflict.schedule.location }}
              </p>
            </div>
          </div>

          <p class="text-gray-700 mt-4 font-medium">
            Do you still want to register for this training?
          </p>
        </div>

        <div class="flex justify-end gap-3 mt-6 w-full">
          <button
            type="button"
            class="btn btn-outline btn-sm px-4"
            @click="cancelConflictRegistration"
          >
            Cancel
          </button>

          <button
            type="button"
            class="btn bg-customButton hover:bg-dark-slate text-white btn-sm px-4"
            @click="confirmConflictRegistration"
          >
            Yes, Register Anyway
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Training Details Modal */
.training-details-modal {
  background: #fff !important;
  padding: 2rem;
  border-radius: 1rem;
  width: min(95vw, 900px);
  max-width: 900px;
  max-height: 90vh;
  position: relative;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  animation: fadeIn 0.25s ease;
  z-index: 2100;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  display: flex;
  flex-direction: column;
}

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

.modal-close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #4a4a4a;
  transition: color 0.2s;
}

.modal-close-btn:hover {
  color: #000;
}

.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 16px;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
}

.training-info {
  margin: 0.4rem 0;
  color: #333;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
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

.schedule-card {
  background: #f5f5f5;
  border-radius: 10px;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  transition: all 0.3s ease;
  cursor: pointer;
  border: 1px solid transparent;
}

.schedule-card:hover {
  background: #e8e8e8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #d1d5db;
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
  word-wrap: break-word;
  overflow-wrap: break-word;
}

/* Force word wrapping for all text content */
.training-details-modal * {
  word-wrap: break-word;
  overflow-wrap: break-word;
  max-width: 100%;
}

.training-details-modal p,
.training-details-modal span,
.training-details-modal div,
.training-details-modal h3,
.training-details-modal strong {
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
}

.schedule-date-time span,
.schedule-location span {
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
}

.schedule-link .training-link:hover {
  color: #2563eb;
}

</style>
