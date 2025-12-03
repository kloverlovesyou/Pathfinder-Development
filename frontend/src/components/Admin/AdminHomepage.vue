<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { getPDFUrl } from "@/lib/supabase";

const organizations = ref([]);
const selectedOrg = ref(null);
const approvedOrganizations = ref([]);
const activeTab = ref("approved"); // "approved" or "rejected"
const rejectedOrganizations = ref([]); // to store rejected organizations
const loadingApproved = ref(true);
const loadingRejected = ref(true);
const loadingPending = ref(true);

// Reject modal state
const rejectModal = ref(false);
const rejectReason = ref("");
const rejectOrgID = ref(null); // target organization being rejected
const toastMessage = ref("");
const toastType = ref("success"); // "success" or "error"
// Requirement modal state
const requirementModal = ref(false);
const requirementUrl = ref(null);
const requirementOrgName = ref("");

// Open reject modal
function openRejectModal(id) {
  rejectOrgID.value = id;
  rejectReason.value = "";
  rejectModal.value = true;
}

// Submit rejection
async function submitRejection() {
  if (!rejectReason.value.trim()) {
    showToast("Please enter a reason for rejection.", "error"); // use toast instead of alert
    return;
  }

  try {
    const res = await axios.post(
      import.meta.env.VITE_API_BASE_URL + `/organization/${rejectOrgID.value}/reject`,
      { reason: rejectReason.value }
    );

    // Remove the rejected org from the pending list
    const rejectedOrg = organizations.value.find(o => o.organizationID === rejectOrgID.value);
    organizations.value = organizations.value.filter(
      (o) => o.organizationID !== rejectOrgID.value
    );

    // Add rejected org to rejectedOrganizations list
    if (rejectedOrg) {
      rejectedOrg.rejectionReason = res.data.rejectionReason; // include reason from API
      rejectedOrganizations.value.push(rejectedOrg);
    }

    // Close modal
    rejectModal.value = false;
    rejectReason.value = "";
    rejectOrgID.value = null;

    showToast("Organization rejected successfully.", "success");
  } catch (err) {
    console.error("Rejection failed:", err.response?.data || err.message);
    showToast("Failed to reject organization.", "error");
  }
}

// Load rejected organizations
async function loadRejectedOrganizations() {
  loadingRejected.value = true; // start loading
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/admin/rejected-organizations");

    // Assign the data; adjust if your API returns { data: [...] }
    rejectedOrganizations.value = res.data;
  } catch (err) {
    console.error("Error loading rejected organizations:", err);
  } finally {
    loadingRejected.value = false; // stop loading
  }
}


// Fetch all pending organizations
async function loadPendingOrganizations() {
  loadingPending.value = true;
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/admin/pending-organizations");
    organizations.value = res.data;
  } catch (err) {
    console.error("Error loading organizations:", err);
  } finally {
    loadingPending.value = false;
  }
}

// Open details modal
function openModal(org) {
  selectedOrg.value = org;
}

// Approve Organization
// Approve Organization
async function acceptOrg(id) {
  try {
    const res = await axios.post(
      import.meta.env.VITE_API_BASE_URL + `/organization/${id}/approve`
    );

    // Remove approved org from the pending list
    organizations.value = organizations.value.filter((o) => o.organizationID !== id);

    // Optionally, add to approvedOrganizations list immediately
    const approvedOrg = res.data.organization; // adjust if your API returns the org details
    if (approvedOrg) {
      approvedOrganizations.value.push(approvedOrg);
    }

    // Close modal if open
    if (selectedOrg.value?.organizationID === id) {
      selectedOrg.value = null;
    }

    // Show success toast
    showToast("Organization approved and email sent successfully!", "success");
  } catch (err) {
    console.error("Approval failed:", err.response?.data || err.message);

    // Show error toast
    showToast("Failed to approve organization or send email.", "error");
  }
}

async function loadApprovedOrganizations() {
  loadingApproved.value = true; // start loading
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/admin/approved-organizations");
    // Check if API returns an array directly
    approvedOrganizations.value = res.data; 
    // Or if API wraps it in 'data'
    // approvedOrganizations.value = res.data.data;
  } catch (err) {
    console.error("Error loading approved organizations:", err);
  } finally {
    loadingApproved.value = false; // stop loading
  }
}

// Format date as "Month Day, Year"
function formatDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric"
  });
}

// View requirement for organization
function viewRequirement(organizationID) {
  const org = organizations.value.find(o => o.organizationID === organizationID);
  
  if (!org) {
    showToast("Organization not found.", "error");
    return;
  }

  const requirementPath = org.registrationRequirements || org.RegistrationRequirements;
  
  if (!requirementPath) {
    showToast("No requirement file found for this organization.", "error");
    return;
  }

  try {
    // Generate PDF URL from Supabase
    const pdfUrl = getPDFUrl(requirementPath, "Requirements");
    
    if (pdfUrl) {
      requirementUrl.value = pdfUrl;
      requirementOrgName.value = org.organizationName || org.name || "Organization";
      requirementModal.value = true;
    } else {
      showToast("Failed to load requirement file. Please try again.", "error");
    }
  } catch (error) {
    console.error("Error viewing requirement:", error);
    showToast("Failed to view requirement. Please try again.", "error");
  }
}

// Show toast message
function showToast(message, type = "success") {
  toastMessage.value = message;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = "";
  }, 3000);
}

onMounted(() => {
  loadPendingOrganizations();
  loadApprovedOrganizations();
  loadRejectedOrganizations();
});
</script>

<template>
  <div class="min-h-screen p-3 rounded-lg font-poppins bg-gray-50">
    <!-- Main Area -->
    <div class="bg-white rounded-lg shadow p-6 flex-1">
      <header class="sticky top-0 z-10 h-16 text-black flex items-center px-4">
        <h1>
          <span class="block text-sm">Organization Account</span>
          <span class="block font-bold text-3xl">For Verification</span>
        </h1>
      </header>

      <section class="mt-4">
        <div v-if="loadingPending" class="flex flex-col items-center justify-center space-y-2 py-10">
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
          <p class="text-gray-500">Loading organizations...</p>
        </div>
        <div v-else-if="organizations.length" class="space-y-4">
          <div
            v-for="org in organizations"
            :key="org.organizationID"
            @click="openModal(org)"
            class="p-2 border rounded-lg cursor-pointer hover:bg-gray-50 flex justify-between items-center"
          >
            <div>
              <h3 class="text-md font-medium">
                {{ org.organizationName ?? org.name }}
              </h3>
            </div>

            <div class="flex space-x-2">
              <button
                @click.stop="acceptOrg(org.organizationID)"
                class="px-4 py-2 text-white bg-customButton hover:bg-dark-slate rounded-lg transition"
              >
                Accept
              </button>

              <button
                @click.stop="openRejectModal(org.organizationID)"
                class="px-4 py-2 text-black bg-gray-400 hover:bg-gray-500 rounded-lg transition"
              >
                Reject
              </button>

              <button
                @click.stop="viewRequirement(org.organizationID)"
                class="px-4 py-2 text-white bg-customButton hover:bg-dark-slate rounded-lg transition"
              >
                View Requirements
              </button>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <p v-else class="text-gray-500 italic">
          No organizations waiting for verification.
        </p>

      </section>

      <section class="mt-10">
        <h2 class="text-xl font-bold mb-3">Organization List</h2>

        <!-- Tabs -->
        <div class="flex space-x-4 mb-4 border-b">
          <button
            :class="activeTab === 'approved' ? 'border-b-2 border-blue-600 font-semibold' : 'text-gray-500'"
            @click="activeTab = 'approved'"
            class="pb-2"
          >
            Approved
          </button>
          <button
            :class="activeTab === 'rejected' ? 'border-b-2 border-red-600 font-semibold' : 'text-gray-500'"
            @click="activeTab = 'rejected'"
            class="pb-2"
          >
            Rejected
          </button>
        </div>

        <!-- Tab content -->
        <div v-if="activeTab === 'approved'">
          <!-- Loading State -->
          <div v-if="loadingApproved" class="flex flex-col items-center justify-center space-y-2 py-10">
            <svg
              class="animate-spin h-10 w-10 text-green-600"
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
            <p class="text-gray-500">Loading approved organizations...</p>
          </div>

          <!-- Approved Organizations List -->
          <div v-else-if="approvedOrganizations.length" class="space-y-3">
            <div
              v-for="org in approvedOrganizations"
              :key="org.organizationID"
              @click="openModal(org)"
              class="p-3 border rounded-lg bg-green-50 flex justify-between items-center cursor-pointer hover:bg-green-100 transition"
            >
              <div>
                <h3 class="font-semibold">{{ org.name }}</h3>
                <p class="text-sm text-gray-600">{{ org.emailAddress }}</p>
                <p class="text-sm text-gray-600">{{ formatDate(org.statusDate) }}</p>
              </div>

              <span class="flex items-center justify-center w-20 h-8 bg-green-600 text-white rounded-lg text-sm">
                Approved
              </span>
            </div>
          </div>

<!-- No organizations -->
<p v-else class="text-gray-500 italic">No approved organizations found.</p>
        </div>

        <div v-else-if="activeTab === 'rejected'">
  <!-- Loading State -->
  <div v-if="loadingRejected" class="flex flex-col items-center justify-center space-y-2 py-10">
    <svg
      class="animate-spin h-10 w-10 text-red-600"
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
    <p class="text-gray-500">Loading rejected organizations...</p>
  </div>

    <!-- Rejected Organizations List -->
    <div v-else-if="rejectedOrganizations.length" class="space-y-3">
      <div
        v-for="org in rejectedOrganizations"
        :key="org.organizationID"
        @click="openModal(org)"
        class="p-3 border rounded-lg bg-red-50 flex justify-between cursor-pointer hover:bg-red-100 transition"
      >
        <div>
          <h3 class="font-semibold">{{ org.name }}</h3>
          <p class="text-sm text-gray-600">{{ org.emailAddress }}</p>
          <p class="text-sm text-gray-600">{{ formatDate(org.statusDate) }}</p>
        </div>

        <span class="px-3 py-1 bg-red-600 text-white rounded-lg text-sm">
          Rejected
        </span>
      </div>
    </div>

    <!-- No rejected organizations -->
    <p v-else class="text-gray-500 italic">No rejected organizations found.</p>
  </div>
      </section>
    </div>

    <!-- Modal -->
    <div
      v-if="selectedOrg"
      class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-md"
      @click.self="selectedOrg = null"
    >
      <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button
          @click="selectedOrg = null"
          class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
        >
          ✕
        </button>

        <h2 class="text-xl font-semibold mb-4">Organization Details</h2>

        <div class="space-y-2">
          <p><strong>Name:</strong> {{ selectedOrg.organizationName ?? selectedOrg.name }}</p>
          <p><strong>Location:</strong> {{ selectedOrg.location }}</p>
          <p>
            <strong>Website:</strong>
            <a
              :href="selectedOrg.websiteURL"
              target="_blank"
              class="text-blue-600 underline"
            >
              {{ selectedOrg.websiteURL }}
            </a>
          </p>
          <p><strong>Email:</strong> {{ selectedOrg.emailAddress }}</p>
          <p v-if="selectedOrg.phoneNumber"><strong>Phone:</strong> {{ selectedOrg.phoneNumber }}</p>
          <p v-if="selectedOrg.statusDate">
            <strong>Status Date:</strong> {{ formatDate(selectedOrg.statusDate) }}
          </p>
          <p v-if="selectedOrg.status">
            <strong>Status:</strong> 
            <span 
              :class="{
                'text-green-600 font-semibold': selectedOrg.status === 'approved',
                'text-red-600 font-semibold': selectedOrg.status === 'rejected',
                'text-yellow-600 font-semibold': selectedOrg.status === 'pending'
              }"
            >
              {{ selectedOrg.status.charAt(0).toUpperCase() + selectedOrg.status.slice(1) }}
            </span>
          </p>
          <div v-if="selectedOrg.rejectionReason" class="mt-3 p-3 bg-red-50 rounded-lg">
            <p class="text-sm font-semibold text-red-800 mb-1">Rejection Reason:</p>
            <p class="text-sm text-red-700">{{ selectedOrg.rejectionReason }}</p>
          </div>
        </div>

        <!-- Show Accept/Reject buttons only for pending organizations -->
        <div v-if="selectedOrg.status === 'pending'" class="mt-6 flex justify-end space-x-2">
          <button
            @click="openRejectModal(selectedOrg.organizationID)"
            class="px-4 py-2 text-black bg-gray-400 hover:bg-gray-500 rounded-lg"
          >
            Reject
          </button>
          <button
            @click="acceptOrg(selectedOrg.organizationID)"
            class="px-4 py-2 text-white bg-customButton hover:bg-dark-slate rounded-lg"
          >
            Accept
          </button>
        </div>
      </div>
    </div>


    <!-- Reject Reason Modal -->
    <div
      v-if="rejectModal"
      class="fixed inset-0 z-50 flex items-center justify-center"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
        <button
          @click="rejectModal = false"
          class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
        >
          ✕
        </button>

        <h2 class="text-xl font-semibold mb-4">Reject Organization</h2>

        <label class="block mb-2 font-medium">Reason for rejection:</label>
        <textarea
          v-model="rejectReason"
          class="w-full border p-2 rounded-md"
          rows="4"
          placeholder="Enter reason..."
        ></textarea>

        <div class="mt-4 flex justify-end space-x-2">
          <button
            @click="rejectModal = false"
            class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-black rounded-lg"
          >
            Cancel
          </button>

          <button
            @click="submitRejection"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg"
          >
            Submit
          </button>
        </div>
      </div>
    </div>

    <!-- Requirement View Modal -->
    <div
      v-if="requirementModal"
      class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-md"
      @click.self="requirementModal = false"
    >
      <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl h-[90vh] flex flex-col relative">
        <div class="flex justify-between items-center p-4 border-b">
          <h2 class="text-xl font-semibold">
            Requirements - {{ requirementOrgName }}
          </h2>
          <button
            @click="requirementModal = false"
            class="text-gray-400 hover:text-gray-600 text-2xl font-bold"
          >
            ✕
          </button>
        </div>
        
        <div class="flex-1 overflow-hidden">
          <iframe
            v-if="requirementUrl"
            :src="requirementUrl"
            class="w-full h-full border-0"
            frameborder="0"
          ></iframe>
          <div v-else class="flex items-center justify-center h-full">
            <p class="text-gray-500">Loading requirement file...</p>
          </div>
        </div>

        <div class="p-4 border-t flex justify-end">
          <button
            @click="requirementModal = false"
            class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-black rounded-lg"
          >
            Close
          </button>
          <a
            v-if="requirementUrl"
            :href="requirementUrl"
            target="_blank"
            class="ml-2 px-4 py-2 bg-customButton hover:bg-dark-slate text-white rounded-lg"
          >
            Open in New Tab
          </a>
        </div>
      </div>
    </div>

          <!-- Toast -->
      <div
        v-if="toastMessage"
        :class="[
          'fixed top-5 right-5 px-4 py-2 rounded shadow-md transition-all z-50',
          toastType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        ]"
      >
        {{ toastMessage }}
      </div>
  </div>
</template>

<style scoped>
/* Optional: modal smooth fade-in */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
