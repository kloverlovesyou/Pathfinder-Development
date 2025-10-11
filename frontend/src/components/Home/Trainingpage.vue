<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";

// Example organizations (replace with API later)
const organizations = ref([
  { id: 1, name: "Global Tech Institute" },
  { id: 2, name: "Future Skills Academy" },
]);

// Trainings data
const trainings = ref([
  {
    id: 1,
    title: "Professional development in emerging technologies",
    description: "Learn about cutting-edge technologies and skills.",
    mode: "Onsite",
    schedule: "2025-11-24T13:30:00",
    location: "Tech Hall, Manila",
    trainingLink: "https://example.com/training/1",
    registrationLink: "https://example.com/register/1",
    organizationID: 1,
  },
  {
    id: 2,
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    description: "Understand AI’s role in daily decision-making.",
    mode: "Online",
    schedule: "2025-09-20T19:30:00",
    location: "AI Center, Cebu",
    trainingLink: "https://example.com/training/2",
    registrationLink: "https://example.com/register/2",
    organizationID: 2,
  },
]);

// Modal + notifications
const selectedTraining = ref(null);
const isModalOpen = ref(false);

const trainingsWithOrg = computed(() =>
  trainings.value.map((t) => {
    const org = organizations.value.find((o) => o.id === t.organizationID);
    return {
      ...t,

      organizationName: org ? org.name : "Unknown",
      formattedSchedule: new Date(t.schedule).toLocaleString("en-US", {
        dateStyle: "medium",
        timeStyle: "short",
      }),
    };
  })
);

// Modal controls
function openTrainingModal(training) {
  selectedTraining.value = training;
  isModalOpen.value = true;
}
function closeModal() {
  isModalOpen.value = false;
  selectedTraining.value = null;
}

const bookmarkedTrainings = ref([]); // stores IDs of bookmarked trainings

function toggleBookmark(trainingId) {
  if (bookmarkedTrainings.value.includes(trainingId)) {
    bookmarkedTrainings.value = bookmarkedTrainings.value.filter(
      (id) => id !== trainingId
    );
  } else {
    bookmarkedTrainings.value.push(trainingId);
  }
}

function isTrainingBookmarked(trainingId) {
  return bookmarkedTrainings.value.includes(trainingId);
}

// Fetch from API
onMounted(async () => {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/trainings");
    trainings.value = response.data;
  } catch (error) {
    console.error("Error fetching trainings:", error);
  }
});
</script>

<template>
  <main class="font-poppins">
    <!-- Header -->

    <div class="bg-white m-3 p-4 rounded-lg">
      <h2 class="text-2xl font-bold mb-3 sticky top-0 bg-white z-10">
        Training
      </h2>
      <!-- Training Cards -->
      <div class="space-y-4">
        <div
          v-for="training in trainingsWithOrg"
          :key="training.id"
          class="p-4 bg-blue-gray rounded-lg relative hover:bg-gray-300 transition cursor-pointer"
          @click="openTrainingModal(training)"
        >
          <!-- Card content -->
          <h3 class="font-semibold text-lg">{{ training.title }}</h3>
          <p class="text-gray-700 font-medium">
            {{ training.organizationName }}
          </p>
        </div>
      </div>
    </div>
    <!-- Training Details Modal -->
    <dialog v-if="isModalOpen" open class="modal">
      <div class="modal-box rounded-none relative w-full h-full sm:w-auto">
        <!-- Close (X) Button -->
        <button
          class="btn btn-sm btn-circle absolute right-2 top-2 z-10 bg-transparent border-0"
          @click="closeModal"
        >
          ✕
        </button>

        <div
          v-if="selectedTraining"
          class="p-6 font-poppins overflow-y-auto h-full sm:h-auto"
        >
          <h1 class="text-2xl font-bold mb-2">{{ selectedTraining.title }}</h1>
          <p class="mb-2">{{ selectedTraining.organizationName }}</p>

          <!-- Action buttons -->
          <div class="flex gap-2 justify-end mb-4">
            <button
              class="rounded-lg flex items-center gap-2 px-3 py-2 border border-gray-300 hover:bg-gray-100 transition"
              @click.stop="toggleBookmark(selectedTraining.id)"
            >
              <span v-if="!isTrainingBookmarked(selectedTraining.id)">
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M12.89 5.87988H5.10999C3.39999 5.87988 2 7.27987 2 8.98987V20.3499C2 21.7999 3.04 22.4199 4.31 21.7099L8.23999 19.5199C8.65999 19.2899 9.34 19.2899 9.75 19.5199L13.68 21.7099C14.95 22.4199 15.99 21.7999 15.99 20.3499V8.98987C16 7.27987 14.6 5.87988 12.89 5.87988Z"
                    stroke="#6682A3"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M16 8.98987V20.3499C16 21.7999 14.96 22.4099 13.69 21.7099L9.76001 19.5199C9.34001 19.2899 8.65999 19.2899 8.23999 19.5199L4.31 21.7099C3.04 22.4099 2 21.7999 2 20.3499V8.98987C2 7.27987 3.39999 5.87988 5.10999 5.87988H12.89C14.6 5.87988 16 7.27987 16 8.98987Z"
                    stroke="#6682A3"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M22 5.10999V16.47C22 17.92 20.96 18.53 19.69 17.83L16 15.77V8.98999C16 7.27999 14.6 5.88 12.89 5.88H8V5.10999C8 3.39999 9.39999 2 11.11 2H18.89C20.6 2 22 3.39999 22 5.10999Z"
                    stroke="#6682A3"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>

              <!-- Bookmarked (filled) -->
              <span v-else>
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M12.89 5.87988H5.11C3.4 5.87988 2 7.27988 2 8.98988V20.3499C2 21.7999 3.04 22.4199 4.31 21.7099L8.24 19.5199C8.66 19.2899 9.34 19.2899 9.75 19.5199L13.68 21.7099C14.96 22.4099 16 21.7999 16 20.3499V8.98988C16 7.27988 14.6 5.87988 12.89 5.87988Z"
                    fill="#44576D"
                  />
                  <path
                    d="M22 5.11V16.47C22 17.92 20.96 18.53 19.69 17.83L17.76 16.75C17.6 16.66 17.5 16.49 17.5 16.31V8.99C17.5 6.45 15.43 4.38 12.89 4.38H8.82C8.45 4.38 8.19 3.99 8.36 3.67C8.88 2.68 9.92 2 11.11 2H18.89C20.6 2 22 3.4 22 5.11Z"
                    fill="#44576D"
                  />
                </svg>
              </span>
            </button>
            <button
              @click.stop="registerForTraining"
              class="btn bg-customButton hover:bg-dark-slate text-white"
            >
              Register
            </button>
          </div>

          <div class="divider"></div>

          <p class="text-gray-600 mb-2">
            <strong>Mode:</strong> {{ selectedTraining.mode }}
          </p>
          <p class="mb-2">
            <strong>Schedule:</strong> {{ selectedTraining.formattedSchedule }}
          </p>
          <p class="mb-2">
            <strong>Location:</strong> {{ selectedTraining.location }}
          </p>
          <p class="mb-4">
            <strong>Description:</strong> {{ selectedTraining.description }}
          </p>
        </div>
      </div>

      <!-- Backdrop -->
      <form method="dialog" class="modal-backdrop" @click="closeModal">
        <button>close</button>
      </form>
    </dialog>

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
