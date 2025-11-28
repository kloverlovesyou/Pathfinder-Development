<template>
  <div class="min-h-screen p-6 bg-gray-50 font-poppins">
    <!-- Header -->
    <header class="mb-6">
      <h1 class="text-3xl font-bold">Approved Organizations</h1>
    </header>

    <!-- Approved Organizations List -->
    <section>
      <div v-if="loading">Loading approved organizations...</div>

      <div v-else-if="approvedOrganizations.length === 0">
        <p class="text-gray-500 italic">No approved organizations found.</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="org in approvedOrganizations"
          :key="org.organizationID"
          class="p-3 border rounded-lg bg-green-50 flex justify-between items-center"
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
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const approvedOrganizations = ref([]);
const loading = ref(true);

async function loadApprovedOrganizations() {
  try {
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/admin/approved-organizations"
    );
    approvedOrganizations.value = res.data;
  } catch (err) {
    console.error("Error loading approved organizations:", err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadApprovedOrganizations();
});
</script>

<style scoped>
/* Optional: spacing, font, and layout */
</style>