<script setup>
import { ref, computed, watch, nextTick } from "vue";
import axios from "axios";

// Props from parent
const props = defineProps({
  show: Boolean,
  career: Object,
  myApplications: [Set, Object],
});

const emits = defineEmits(["close", "update-applications"]);

const showUploadModal = ref(false);
const uploadedFile = ref(null);
const toasts = ref([]);
const bookmarkLoading = ref(false);
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

const modalRef = ref(null);

watch(
  () => props.show,
  async (newVal) => {
    await nextTick();
    if (newVal) modalRef.value?.showModal();
    else modalRef.value?.close();
  }
);

// --- Toast Helper ---
function addToast(message, type = "info") {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }, 3000);
}

const isApplied = computed(() =>
  props.myApplications.has(props.career?.careerID ?? props.career?.id)
);

// --- Upload Modal Handlers ---
function openUploadModal() {
  showUploadModal.value = true;
}

function closeUploadModal() {
  showUploadModal.value = false;
  uploadedFile.value = null;
}

function handleFileUpload(event) {
  uploadedFile.value = event.target.files[0];
  console.log("Uploaded file:", uploadedFile.value);
}

// --- Submit Application ---
async function submitApplication() {
  if (!props.career) return;

  const token = localStorage.getItem("token");
  if (!token) {
    addToast("PLEASE LOG IN FIRST", "accent");
    return;
  }

  if (!uploadedFile.value) {
    addToast("PLEASE ATTACH YOUR REQUIREMENTS PDF", "accent");
    return;
  }

  try {
    const formData = new FormData();
    formData.append("careerID", props.career.careerID ?? props.career.id);
    formData.append("requirement_directory", uploadedFile.value);

    await axios.post(`${API_BASE_URL}/applications`, formData, {
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "multipart/form-data",
      },
    });

    addToast("APPLICATION SUBMITTED SUCCESSFULLY", "success");

    const id = props.career.careerID ?? props.career.id;
    props.myApplications.add(id);
    emits("update-applications", new Set(props.myApplications));

    closeUploadModal();
    emits("close");
  } catch (error) {
    if (error.response?.status === 409) {
      addToast("YOU ALREADY APPLIED TO THIS CAREER", "accent");
    } else if (error.response?.status === 401) {
      addToast("UNAUTHORIZED. PLEASE LOG IN AGAIN", "accent");
    } else if (error.response?.status === 422) {
      const errorMsg =
        error.response?.data?.message ||
        error.response?.data?.errors?.[0] ||
        "INVALID INPUT. ONLY PDF UP TO 5MB";
      addToast(errorMsg, "accent");
    } else {
      const errorMsg =
        error.response?.data?.message ||
        error.response?.data?.error ||
        "FAILED TO SUBMIT APPLICATION";
      addToast(errorMsg, "accent");
      console.error("Application submission error:", error.response?.data || error);
    }
  }
}

function formatDateTime(dateStr) {
  if (!dateStr) return "N/A";
  const date = new Date(dateStr);
  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}
</script>

<template>
  <div>
    <!-- Career Modal -->
    <dialog v-if="show" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins">
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="$emit('close')">
          ✕
        </button>

        <h2 class="text-xl font-bold mb-2">{{ career.position }}</h2>
        <p class="text-sm text-gray-600 mb-2">
          Organization: {{ career.organization }}
        </p>

        <div class="my-4 flex justify-end gap-2">
          <button
            v-if="!isApplied"
            class="btn btn-sm bg-customButton text-white"
            @click="openUploadModal"
          >
            Apply
          </button>

          <button v-else class="btn btn-sm bg-gray-500 text-white" disabled>
            Applied
          </button>
        </div>

        <!-- Career Details -->
        <p><strong>Details:</strong> {{ career.detailsAndInstructions }}</p>
        <p><strong>Qualifications:</strong> {{ career.qualifications }}</p>
        <p><strong>Requirements:</strong> {{ career.requirements }}</p>
        <p>
          <strong>Application Address:</strong>
          {{ career.applicationLetterAddress }}
        </p>
        <p>
          <strong>Deadline:</strong>
          {{ formatDateTime(career.deadlineOfSubmission) }}
        </p>
      </div>
    </dialog>

    <!-- Upload Modal -->
    <dialog v-if="showUploadModal" open class="modal sm:modal-middle">
      <div class="modal-box max-w-lg relative font-poppins">
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="closeUploadModal">
          ✕
        </button>

        <h2 class="text-xl font-bold mb-4">Apply for {{ career?.position }}</h2>

        <form @submit.prevent="submitApplication">
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1">
              Upload PDF Requirements
            </label>
            <input type="file" accept="application/pdf" @change="handleFileUpload" required
              class="file-input file-input-bordered w-full" />
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" class="btn btn-outline btn-sm" @click="closeUploadModal">
              Cancel
            </button>
            <button type="submit" class="btn bg-customButton hover:bg-dark-slate text-white btn-sm">
              Submit
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <!-- Toasts -->
    <div class="toast toast-end toast-top z-50">
      <div v-for="toast in toasts" :key="toast.id" class="alert" :class="{
        'alert-info': toast.type === 'info',
        'alert-success': toast.type === 'success',
        'alert-accent': toast.type === 'accent',
      }">
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>
