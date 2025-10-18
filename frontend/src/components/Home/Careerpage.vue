<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const organizations = ref([]);
const careers = ref([]);
const toasts = ref([]);
const myApplications = ref(new Set());

function addToast(message, type = 'info'){
  const id = Date.now();
  toasts.value.push({id, message, type});
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  }, 3000);
}

async function fetchMyApplications() {
  try {
    const token = localStorage.getItem('token');
    if (!token) return;
    const res = await axios.get('http://127.0.0.1:8000/api/applications', {
      headers: { Authorization: `Bearer ${token}` },
    });
    myApplications.value = new Set(res.data.map(a => a.careerID));
  } catch (_) {}
}

onMounted(async () => {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/careers");
    careers.value = response.data;
  } catch (error) {
    console.error("Error fetching careers:", error);
  }
  await fetchMyApplications();
});


onMounted(async () => {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/careers");
    careers.value = response.data;
  } catch (error) {
    console.error("Error fetching careers:", error);
  }
});

// Merge careers with organization name
const careersWithOrg = computed(() =>
  careers.value.map((career) => ({
    ...career,
    organizationName: career.organizationName || "Unknown",
  }))
);

const showModal = ref(false);
const showUploadModal = ref(false);
const selectedCareer = ref(null);
const isClicked = ref(false);
const uploadedFile = ref(null);

function openModal(career) {
  selectedCareer.value = career;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedCareer.value = null;
}

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
    const token = localStorage.getItem('token');
    if (!token) {
      addToast('PLEASE LOG IN FIRST', 'accent');
      return;
    }

    const form = new FormData();
    form.append('careerID', selectedCareer.value.careerID ?? selectedCareer.value.id);
    if (uploadedFile.value) {
      form.append('requirements', uploadedFile.value);
    }

    await axios.post('http://127.0.0.1:8000/api/applications', form, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      },
    });

    addToast('APPLICATION SUBMITTED SUCCESSFULLY', 'success');
    // Mark as applied in UI
    const id = selectedCareer.value.careerID ?? selectedCareer.value.id;
    myApplications.value.add(id);

    closeUploadModal();
    closeModal();
  } catch (error) {
    if (error.response?.status === 409) {
      addToast('YOU ALREADY APPLIED TO THIS CAREER', 'accent');
    } else if (error.response?.status === 401) {
      addToast('UNAUTHORIZED. PLEASE LOG IN AGAIN', 'accent');
    } else if (error.response?.status === 422) {
      addToast('INVALID INPUT. ONLY PDF UP TO 5MB', 'accent');
    } else {
      addToast('FAILED TO SUBMIT APPLICATION', 'accent');
    }
  }
}


// Track bookmarked careers (store IDs)
const bookmarkedCareers = ref(new Set());

// Check if a career is bookmarked
function isCareerBookmarked(careerId) {
  return bookmarkedCareers.value.has(careerId);
}

// Toggle bookmark
function toggleCareerBookmark(careerId) {
  if (bookmarkedCareers.value.has(careerId)) {
    bookmarkedCareers.value.delete(careerId);
  } else {
    bookmarkedCareers.value.add(careerId);
  }
}
</script>

<template>
  <main class="font-poppins">
    <!-- Header -->

    <div class="bg-white m-3 p-4 rounded-lg">
      <h2 class="text-2xl font-bold mb-3 sticky top-0 bg-white z-10">Career</h2>
      <!-- Career Cards -->
      <div class="space-y-4">
        <div
          v-for="career in careersWithOrg"
          :key="career.id"
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

    <!-- Career Details Modal -->
    <dialog v-if="showModal" open class="modal">
      <div class="modal-box rounded-none relative w-full h-full sm:w-auto">
        <!-- Close (X) Button -->
        <button
          class="btn btn-sm btn-circle absolute right-2 top-2 z-10 bg-transparent border-0"
          @click="closeModal"
        >
          ✕
        </button>

        <div
          v-if="selectedCareer"
          class="p-6 font-poppins overflow-y-auto h-full sm:h-auto"
        >
          <h1 class="text-2xl font-bold mb-2">{{ selectedCareer.position }}</h1>
          <p class="mb-2">{{ selectedCareer.organizationName }}</p>

          <!-- Action buttons -->
          <div class="flex gap-2 justify-end mb-4">
            <button
              class="rounded-lg flex items-center gap-2 px-3 py-2 border border-gray-300 hover:bg-gray-100 transition"
              @click.stop="toggleCareerBookmark(selectedCareer.id)"
            >
              <!-- Bookmark toggle -->
              <span v-if="!isCareerBookmarked(selectedCareer.id)">
                <!-- Outline bookmark -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path
                    d="M12.89 5.87988H5.10999C3.39999 5.87988 2 7.27987 2 8.98987V20.3499C2 21.7999 3.04 22.4199 4.31 21.7099L8.23999 19.5199C8.65999 19.2899 9.34 19.2899 9.75 19.5199L13.68 21.7099C14.95 22.4199 15.99 21.7999 15.99 20.3499V8.98987C16 7.27987 14.6 5.87988 12.89 5.87988Z"
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
              <span v-else>
                <!-- Filled bookmark -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
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
              @click.stop="openUploadModal"
              class="btn btn-primary bg-customButton hover:bg-dark-slate text-white"
              :disabled="myApplications.has(selectedCareer.careerID ?? selectedCareer.id)"
              >
              {{ myApplications.has(selectedCareer.careerID ?? selectedCareer.id) ? 'Applied' : 'Apply' }}
            </button>
          </div>

          <div class="divider"></div>

          <!-- Details -->
          <p v-if="selectedCareer.detailsAndInstructions">
            <span class="font-semibold">Details:</span>
            {{ selectedCareer.detailsAndInstructions }}
          </p>
          <p v-if="selectedCareer.qualifications">
            <span class="font-semibold">Qualifications:</span>
            {{ selectedCareer.qualifications }}
          </p>
          <p v-if="selectedCareer.requirements">
            <span class="font-semibold">Requirements:</span>
            {{ selectedCareer.requirements }}
          </p>
          <p v-if="selectedCareer.applicationLetterAddress">
            <span class="font-semibold">Application Letter Address:</span>
            {{ selectedCareer.applicationLetterAddress }}
          </p>
        </div>
      </div>

      <!-- Backdrop -->
      <form method="dialog" class="modal-backdrop" @click="closeModal">
        <button>close</button>
      </form>
    </dialog>

    <!-- Upload PDF Modal -->
    <dialog
      v-if="showUploadModal"
      open
      class="modal modal-bottom sm:modal-middle"
    >
      <div class="modal-box max-w-lg relative">
        <button
          class="btn btn-sm btn-circle absolute right-2 top-2"
          @click="closeUploadModal"
        >
          ✕
        </button>

        <h2 class="text-xl font-bold mb-4">Application Requirements</h2>
        <p class="mb-4">Compile and consolidate requirements to a PDF file:</p>

        <input
          type="file"
          accept="application/pdf"
          class="file-input file-input-bordered w-full"
          @change="handleFileUpload"
        />

        <div class="flex justify-end gap-2 mt-6">
          <button
            class="btn btn-primary bg-customButton hover:bg-dark-slate text-white"
            @click="submitApplication"
          >
            Submit
          </button>
          <button class="btn" @click="closeUploadModal">Cancel</button>
        </div>
      </div>
      <form method="dialog" class="modal-backdrop" @click="closeUploadModal">
        <button>close</button>
      </form>
    </dialog>
  </main>
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
</template>
