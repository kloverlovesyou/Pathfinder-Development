<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";
import { uploadPDF } from "@/lib/supabase";

// Props from parent
const props = defineProps({
  show: Boolean, // Controls visibility
  career: Object, // Selected career data
  myApplications: [Set, Object], // allow Set or ref(Set)
  bookmarkedCareers: [Set, Object],
});
const actualApplications = computed(() =>
  props.myApplications instanceof Set
    ? props.myApplications
    : props.myApplications?.value
);

const actualBookmarks = computed(() =>
  props.bookmarkedCareers instanceof Set
    ? props.bookmarkedCareers
    : props.bookmarkedCareers?.value
);
const emits = defineEmits(["close", "update-applications", "update-bookmarks"]);
const myApplications = ref(new Set());
const bookmarkedCareers = ref(new Set());

const showUploadModal = ref(false);
const uploadedFile = ref(null);
const toasts = ref([]);
const bookmarkLoading = ref(false);

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

watch(
  () => props.show,
  (val) => console.log("Modal received prop:", val)
);

// --- Check if bookmarked / applied ---
const isBookmarked = computed(() =>
  props.bookmarkedCareers.has(props.career?.careerID ?? props.career?.id)
);

const isApplied = computed(() =>
  props.myApplications.has(props.career?.careerID ?? props.career?.id)
);

// --- Toggle Bookmark ---
async function toggleBookmark() {
  const token = localStorage.getItem("token");
  if (!token) {
    addToast("PLEASE LOG IN FIRST", "accent");
    return;
  }

  const id = props.career?.careerID ?? props.career?.id;
  bookmarkLoading.value = true;

  try {
    if (isBookmarked.value) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/career-bookmarks/${id}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      props.bookmarkedCareers.delete(id);
      emits("update-bookmarks", new Set(props.bookmarkedCareers));
      addToast("Bookmark removed", "success");
    } else {
      await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/career-bookmarks",
        { careerID: id },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      props.bookmarkedCareers.add(id);
      emits("update-bookmarks", new Set(props.bookmarkedCareers));
      addToast("Bookmark added", "success");
    }
  } catch (error) {
    console.error(error);
    addToast("Error toggling bookmark", "accent");
  } finally {
    bookmarkLoading.value = false;
  }
}

// --- Apply Modal Handlers ---
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

  let filePath = null;

  // Upload file to Supabase first (if file is selected)
  if (uploadedFile.value) {
    addToast("Uploading file to Supabase...", "info");
    filePath = await uploadPDF(uploadedFile.value, "Requirements");
    
    if (!filePath) {
      addToast("FAILED TO UPLOAD FILE TO SUPABASE", "accent");
      return;
    }
    addToast("File uploaded successfully!", "success");
  }

  // Send application data to backend (with file path, not the file itself)
  try {
    const payload = {
      careerID: props.career.careerID ?? props.career.id,
      requirement_directory: filePath, // Send the path, not the file
    };

    await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/applications",
      payload,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
      }
    );

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
      addToast("INVALID INPUT. ONLY PDF UP TO 5MB", "accent");
    } else {
      addToast("FAILED TO SUBMIT APPLICATION", "accent");
      console.error("Application submission error:", error);
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

        <!-- Buttons -->
        <div class="my-4 flex justify-end gap-2">
          <button class="btn btn-outline btn-sm" @click="toggleBookmark" :disabled="bookmarkLoading">
            <span v-if="bookmarkLoading" class="loading loading-spinner loading-sm"></span>
            <span v-else>{{ isBookmarked ? "Bookmarked" : "Bookmark" }}</span>
          </button>

          <button v-if="!isApplied" class="btn btn-sm bg-customButton text-white" @click="openUploadModal">
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
