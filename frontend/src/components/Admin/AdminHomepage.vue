<script setup>
import { ref } from "vue";

const organizations = ref([
  {
    id: 1,
    name: "TechNova Corp",
    location: "Cebu City",
    websiteURL: "https://technova.com",
    emailAddress: "contact@technova.com",
  },
  {
    id: 2,
    name: "EduLink Solutions",
    location: "Makati, Manila",
    websiteURL: "https://edulink.ph",
    emailAddress: "info@edulink.ph",
  },
]);

const selectedOrg = ref(null);

function openModal(org) {
  selectedOrg.value = org;
}

function acceptOrg(id) {
  alert(`✅ Organization ID ${id} has been accepted.`);
  selectedOrg.value = null;
  organizations.value = organizations.value.filter((o) => o.id !== id);
}

function rejectOrg(id) {
  alert(`❌ Organization ID ${id} has been rejected.`);
  selectedOrg.value = null;
  organizations.value = organizations.value.filter((o) => o.id !== id);
}
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
            :key="org.id"
            @click="openModal(org)"
            class="p-2 border rounded-lg cursor-pointer hover:bg-gray-50 flex justify-between items-center"
          >
            <div>
              <h3 class="text-md font-medium">
                {{ org.name }}
              </h3>
            </div>

            <div class="flex space-x-2">
              <button
                @click.stop="acceptOrg(org.id)"
                class="px-4 py-2 text-white bg-customButton hover:bg-dark-slate rounded-lg transition"
              >
                Accept
              </button>
              <button
                @click.stop="rejectOrg(org.id)"
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
      class="fixed inset-0 flex items-center justify-center z-50"
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
          <p><strong>Name:</strong> {{ selectedOrg.name }}</p>
          <p><strong>Location:</strong> {{ selectedOrg.location }}</p>
          <p>
            <strong>Website:</strong>
            <a
              :href="selectedOrg.websiteURL"
              target="_blank"
              class="text-blue-600 underline"
              >{{ selectedOrg.websiteURL }}</a
            >
          </p>
          <p><strong>Email:</strong> {{ selectedOrg.emailAddress }}</p>
        </div>

        <div class="mt-6 flex justify-end space-x-2">
          <button
            @click="rejectOrg(selectedOrg.id)"
            class="px-4 py-2 text-black bg-gray-400 hover:bg-gray-500 rounded-lg"
          >
            Reject
          </button>
          <button
            @click="acceptOrg(selectedOrg.id)"
            class="px-4 py-2 text-white bg-customButton hover:bg-dark-slate rounded-lg"
          >
            Accept
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
