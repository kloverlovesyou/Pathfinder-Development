<script setup>
import { ref, computed, watch, nextTick } from "vue";
import axios from "axios";
import { getPDFUrl } from "@/lib/supabase";

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
const unapplyLoading = ref(false);
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

function getCareerId() {
  return props.career?.careerID ?? props.career?.id ?? null;
}

const isApplied = computed(() => {
  const careerId = getCareerId();
  if (!careerId || !props.myApplications) return false;

  if (typeof props.myApplications.has === "function") {
    return props.myApplications.has(careerId);
  }

  if (Array.isArray(props.myApplications)) {
    return props.myApplications.includes(careerId);
  }

  if (
    typeof props.myApplications === "object" &&
    props.myApplications !== null
  ) {
    return !!props.myApplications[careerId];
  }

  return false;
});

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

    const id = getCareerId();
    const updatedSet = new Set(props.myApplications ?? []);
    if (id !== null) {
      updatedSet.add(id);
    }
    emits("update-applications", updatedSet);

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

async function unapplyApplication() {
  if (!props.career) return;
  const token = localStorage.getItem("token");
  if (!token) {
    addToast("PLEASE LOG IN FIRST", "accent");
    return;
  }

  const careerId = getCareerId();
  if (!careerId) {
    addToast("INVALID CAREER DATA", "accent");
    return;
  }

  if (unapplyLoading.value) return;
  unapplyLoading.value = true;

  try {
    await axios.delete(
      `${API_BASE_URL}/applications/career/${careerId}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    addToast("APPLICATION WITHDRAWN", "success");
    const updatedSet = new Set(props.myApplications ?? []);
    updatedSet.delete(careerId);
    emits("update-applications", updatedSet);
    emits("close");
  } catch (error) {
    const errorMsg =
      error.response?.data?.message || "FAILED TO WITHDRAW APPLICATION";
    addToast(errorMsg, "accent");
    console.error("Application withdrawal error:", error.response?.data || error);
  } finally {
    unapplyLoading.value = false;
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

// --- View PDF in new tab ---
function viewPDF(event) {
  event.preventDefault();
  if (!props.career?.pdf_directory) {
    addToast("PDF not available", "accent");
    return;
  }

  try {
    const filePath = props.career.pdf_directory;
    
    // Try Supabase first
    const pdfUrl = getPDFUrl(filePath, "Requirements");
    
    if (pdfUrl) {
      // Open PDF in new tab
      window.open(pdfUrl, '_blank');
      return;
    }
    
    // Fallback: if pdf_directory is already a full URL
    if (filePath.startsWith("http://") || filePath.startsWith("https://")) {
      window.open(filePath, '_blank');
      return;
    }
    
    addToast("Failed to open PDF", "accent");
  } catch (error) {
    console.error("Error opening PDF:", error);
    addToast("Failed to open PDF", "accent");
  }
}
</script>

<template>
  <div>
    <!-- Career Modal -->
    <dialog v-if="show" open class="modal sm:modal-middle">
      <div class="modal-box max-w-3xl relative font-poppins" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word; max-width: 900px; width: min(95vw, 900px);">
        <button class="btn btn-sm btn-circle border-transparent bg-transparent absolute right-2 top-2"
          @click="$emit('close')">
          ✕
        </button>

        <h2 class="text-xl font-bold mb-2">{{ career.position }}</h2>
        <p class="text-sm text-gray-600 mb-2">
          Organization: {{ career.organizationName || career.organization || 'Unknown' }}
        </p>

        <div class="my-4 flex justify-end gap-2">
          <button
            v-if="!isApplied"
            class="btn btn-sm bg-customButton text-white"
            @click="openUploadModal"
          >
            Apply
          </button>

          <button
            v-else
            class="btn btn-sm bg-gray-500 hover:bg-red-700 text-white flex items-center gap-2"
            :disabled="unapplyLoading"
            @click="unapplyApplication"
          >
            <svg
              v-if="unapplyLoading"
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
            <span>{{ unapplyLoading ? "Processing" : "Unapply" }}</span>
          </button>
        </div>

        <!-- Career Details -->
        <p v-if="career.placeOfAssignment"><strong>Place of Assignment:</strong> {{ career.placeOfAssignment }}</p>
        <p v-if="career.details || career.detailsAndInstructions" class="career-text-inline">
          <strong>Details:</strong>
          <span class="career-text-value">{{ career.details || career.detailsAndInstructions }}</span>
        </p>
        <p v-if="career.qualificationStandard" class="career-text-inline">
          <strong>Qualification Standard:</strong>
          <span class="career-text-value">{{ career.qualificationStandard }}</span>
        </p>
        <p v-if="career.postingDate"><strong>Posting Date:</strong> {{ formatDateTime(career.postingDate) }}</p>
        <p>
          <strong>Closing Date:</strong>
          {{ formatDateTime(career.closingDate || career.deadlineOfSubmission) }}
        </p>
        <p v-if="career.trainingsAttendedPercentage !== null && career.trainingsAttendedPercentage !== undefined">
          <strong>Trainings Attended Percentage:</strong> {{ career.trainingsAttendedPercentage }}%
        </p>
        <p v-if="career.pdf_directory">
          <a 
            href="#" 
            @click="viewPDF" 
            class="text-blue-600 hover:underline cursor-pointer ml-2"
          >View Details
          </a>
        </p>
      </div>
    </dialog>

    <!-- Upload Modal -->
    <dialog v-if="showUploadModal" open class="modal sm:modal-middle">
      <div class="modal-box max-w-lg relative font-poppins" style="word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;">
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

<style scoped>
/* Force word wrapping for all text content in modals */
.modal-box {
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  word-break: break-word !important;
  max-width: 900px !important;
  width: min(95vw, 900px) !important;
}

.modal-box * {
  word-wrap: break-word;
  overflow-wrap: break-word;
  max-width: 100%;
}

.modal-box p,
.modal-box span,
.modal-box div,
.modal-box h2,
.modal-box strong,
.modal-box a {
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
}

/* Ensure links don't overflow */
.modal-box a {
  word-break: break-all;
  display: inline-block;
  max-width: 100%;
}

/* Career text inline format to prevent overlap */
.career-text-inline {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  gap: 0.5rem;
  margin: 0.5rem 0;
  width: 100%;
}

.career-text-inline strong {
  flex-shrink: 0;
  white-space: nowrap;
}

.career-text-value {
  flex: 1;
  min-width: 0;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
  line-height: 1.6;
}
</style>
