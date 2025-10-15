<script setup>
import { ref, onMounted, computed, onActivated } from "vue";
import axios from "axios";

// Example organizations (replace with API later)
const organizations = ref([]);

// Trainings data
const trainings = ref([]);

// Modal + notifications
const selectedTraining = ref(null);
const isModalOpen = ref(false);
const toasts = ref([]);
const myRegistrations = ref(new Set());

const trainingsWithOrg = computed(() =>
  trainings.value.map((t) => {
    const org = organizations.value.find((o) => o.id === t.organizationID);
    return {
      ...t,

      organizationName: org ? org.name : "Unknown",
      displaySchedule: t.schedule || "TBD"
    };
  })
);

// Modal controls\
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

//register for training function
async function registerForTraining(training){
  if(!training) return;

  try{
    const token = localStorage.getItem('token');
    if(!token){
      addToast('PLEASE LOG IN FIRST', 'accent');
      return;
    }

    await axios.post(
      'http://127.0.0.1:8000/api/registrations',
      { trainingID: training.trainingID },
      {headers: { Authorization: `Bearer ${token}` } }
    );

    addToast('REGISTRATION SUCCESSFUL!!!', 'success');
    myRegistrations.value.add(training.trainingID);
  } catch (error){
    if(error.response?.status ===409){
      addToast('YOU ARE ALREADY REGISTERED', 'accent');
    }else if(error.response?.status === 401){
      addToast('UNAUTHORIZED PLEASE LOG IN AGAIN', 'accent');
    }else {
      addToast('FAILED TO REGISTER', 'accent');
    }
  }

}

//toast notification function
function addToast(message, type = 'info'){
  const id = Date.now();
  toasts.value.push({id, message, type});
  setTimeout(() => {
    toasts.value = toasts.value.filter(toast => toast.id !== id);
  }, 3000);
}


// Fetch from API
async function fetchTrainings() {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/trainings");
    trainings.value = response.data;
  } catch (error) {
    console.error("Error fetching trainings:", error);
    addToast('FAILED TO LOAD TRAININGS', 'error');
  }
}


async function fetchMyRegistrations(){
  try{
    const token = localStorage.getItem('token');
    if(!token) return;
    const res = await axios.get('http://127.0.0.1:8000/api/registrations', {
      headers: { Authorization: `Bearer ${token}`},
    });
    myRegistrations.value = new Set(res.data.map(r => r.trainingID));
  } catch (_) {}
}


//fetch trainings on mount 
onMounted(async () => {
  await fetchTrainings();
  await fetchMyRegistrations();
});

onActivated(() => {
  fetchTrainings();
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
          v-for="training in trainings"
          :key="training.trainingID"
          class="p-4 bg-blue-gray rounded-lg relative hover:bg-gray-300 transition cursor-pointer"
          @click="openTrainingModal(training)"
        >
          <!-- Card content -->
          <h3 class="font-semibold text-lg">{{ training.title }}</h3>
          <p class="text-gray-700 font-medium">
            {{ training.organization.name}}
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

        <div
          v-if="selectedTraining"
          class="p-6 font-poppins overflow-y-auto h-full sm:h-auto"
        >
          <h1 class="text-2xl font-bold mb-2">{{ selectedTraining.title }}</h1>
          <p><strong>Organization:</strong> {{ selectedTraining.organization.name }}</p>

          <!-- Action buttons -->
          <div class="flex gap-2 justify-end mb-4">
            <button
              class="rounded-lg flex items-center gap-2 px-3 py-2 border border-gray-300 hover:bg-gray-100 transition"
              @click.stop="toggleBookmark(selectedTraining.trainingID)"
            >
              <span v-if="!isTrainingBookmarked(selectedTraining.trainingID)">
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
              class="btn bg-customButton btn-sm text-white"
              :disabled="myRegistrations.has(selectedTraining.trainingID)"
              @click.stop="registerForTraining(selectedTraining)"
            >
              {{ myRegistrations.has(selectedTraining.trainingID) ? 'Registered' : 'Register' }}
            </button>
          </div>

          <div class="divider"></div>

          <p class="text-gray-600 mb-2">
            <strong>Mode:</strong> {{ selectedTraining.mode }}
          </p>
          <p class="mb-2">
            <strong>Schedule:</strong> {{ selectedTraining.schedule }}
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
