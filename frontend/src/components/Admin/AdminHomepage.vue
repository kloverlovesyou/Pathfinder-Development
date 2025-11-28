<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const organizations = ref([]);
const selectedOrg = ref(null);
const approvedOrganizations = ref([]);
const activeTab = ref("approved"); // "approved" or "rejected"
const rejectedOrganizations = ref([]); // to store rejected organizations
const loadingApproved = ref(true);
const loadingRejected = ref(true);
// Reject modal state
const rejectModal = ref(false);
const rejectReason = ref("");
const rejectOrgID = ref(null); // target organization being rejected

// Open reject modal
function openRejectModal(id) {
  rejectOrgID.value = id;
  rejectReason.value = "";
  rejectModal.value = true;
}

// Submit rejection
async function submitRejection() {
  if (!rejectReason.value.trim()) {
    alert("Please enter a reason."); // or showToast
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

    showToast("Organization rejected successfully.");
  } catch (err) {
    console.error("Rejection failed:", err.response?.data || err.message);
    showToast("Failed to reject organization.");
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
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/admin/pending-organizations");
    organizations.value = res.data;
  } catch (err) {
    console.error("Error loading organizations:", err);
  }
}

// Open details modal
function openModal(org) {
  selectedOrg.value = org;
}

// Approve Organization
async function acceptOrg(id) {
  try {
    await axios.post(import.meta.env.VITE_API_BASE_URL + `/organization/${id}/approve`);
    organizations.value = organizations.value.filter((o) => o.organizationID !== id);
    selectedOrg.value = null;
  } catch (err) {
    console.error("Approval failed:", err);
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
        <!-- List of organizations waiting to be verified -->
        <div v-if="organizations.length" class="space-y-4">
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
              class="p-3 border rounded-lg bg-green-50 flex justify-between"
            >
              <div>
                <h3 class="font-semibold">{{ org.name }}</h3>
                <p class="text-sm text-gray-600">{{ org.emailAddress }}</p>
              </div>

              <span class="px-3 py-1 bg-green-600 text-white rounded-lg text-sm">
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
        class="p-3 border rounded-lg bg-red-50 flex justify-between"
      >
        <div>
          <h3 class="font-semibold">{{ org.name }}</h3>
          <p class="text-sm text-gray-600">{{ org.emailAddress }}</p>
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
      class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-20"
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
        </div>

        <div class="mt-6 flex justify-end space-x-2">
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
      class="fixed inset-0 z-50 bg-black bg-opacity-30 flex items-center justify-center"
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
