<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const organizations = ref([]);
const selectedOrg = ref(null);
const rejectModal = ref(false);
const rejectReason = ref("");
const rejectTargetID = ref(null);

function openRejectModal(id) {
  rejectTargetID.value = id;
  rejectReason.value = "";
  rejectModal.value = true;
}

async function submitRejection() {
  try {
    await axios.post(
      import.meta.env.VITE_API_BASE_URL + `/organization/${rejectTargetID.value}/reject`,
      { reason: rejectReason.value }
    );

    organizations.value = organizations.value.filter(
      (o) => o.organizationID !== rejectTargetID.value
    );

    rejectModal.value = false;
    selectedOrg.value = null;
  } catch (err) {
    console.error("Rejection failed:", err);
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

// Reject Organization
async function rejectOrg(id) {
  try {
    await axios.post(import.meta.env.VITE_API_BASE_URL + `/organization/${id}/reject`);
    organizations.value = organizations.value.filter((o) => o.organizationID !== id);
    selectedOrg.value = null;
  } catch (err) {
    console.error("Rejection failed:", err);
  }
}

onMounted(() => {
  loadPendingOrganizations();
});
</script>

<template>
  <div class="min-h-screen p-3 rounded-lg font-poppins bg-gray-50">
    <!-- Main Area -->
    <div class="bg-white rounded-lg shadow p-6 flex-1">
      <header class="sticky top-0 z-10 h-16 text-black flex items-center px-4">
        <h1>
          <span class="block text-sm">Organization Account</span>
          <span class="block font-bold text-3xl">Verification</span>
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
            </div>
          </div>
        </div>

        <p v-else class="text-gray-500 italic">
          No organizations waiting for verification.
        </p>
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
