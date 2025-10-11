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
  // Mock data only for frontend
  {
    trainingID: 1,
    title: "Professional development in emerging technologies",
    schedule: "2025-11-24T13:30:00",
    organizationID: 1,
    location: "Main Hall",
    trainingLink: "#",
    mode: "Onsite",
    description: "Learn about emerging technologies.",
  },
  {
    trainingID: 2,
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    schedule: "2025-09-20T19:30:00",
    organizationID: 2,
    location: "Auditorium",
    trainingLink: "#",
    mode: "Onsite",
    description: "Explore how AI affects daily life.",
  },
  {
    trainingID: 6,
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    schedule: "2025-09-20T19:30:00",
    organizationID: 2,
    location: "Auditorium",
    trainingLink: "#",
    mode: "Onsite",
    description: "Explore how AI affects daily life.",
  },
  {
    trainingID: 7,
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    schedule: "2025-09-20T19:30:00",
    organizationID: 2,
    location: "Auditorium",
    trainingLink: "#",
    mode: "Onsite",
    description: "Explore how AI affects daily life.",
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
    <dialog v-if="isModalOpen" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <!-- Close (X) Button -->
        <button
          class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeModal"
        >
          ✕
        </button>

        <div v-if="selectedTraining">
          <h2 class="text-xl font-bold mb-2">{{ selectedTraining.title }}</h2>
          <p class="text-sm text-gray-600 mb-2">
            {{ selectedTraining.organizationName }}
          </p>
          <div class="my-4 flex justify-end gap-2">
            <button
              class="btn btn-outline btn-sm"
              @click="bookmarkPost(selectedPost)"
            >
              Bookmark
            </button>
            <button
              class="btn bg-customButton btn-sm text-white"
              @click="registerTraining(selectedPost)"
            >
              Register
            </button>
          </div>

          <div class="divider"></div>

          <p>
            <strong>Mode:</strong>
            {{ selectedTraining.mode || "Not specified" }}
          </p>

          <p>
            <strong>Description:</strong> {{ selectedTraining.description }}
          </p>
          <p>
            <strong>Schedule:</strong>
            {{ selectedTraining.formattedScheduleschedule }}
          </p>
          <p><strong>Location:</strong> {{ selectedTraining.location }}</p>
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
