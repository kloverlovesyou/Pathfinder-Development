<template>
  <div class="rejected-container">
    <h1 class="title">Rejected Organizations</h1>

    <!-- Loading -->
    <div v-if="loading">Loading rejected organizations...</div>

    <!-- No Data -->
    <div v-else-if="rejectedOrganizations.length === 0">
      <p>No rejected organizations found.</p>
    </div>

    <!-- Table -->
    <table v-else class="table">
      <thead>
        <tr>
          <th>Organization Name</th>
          <th>Training</th>
          <th>Career</th>
          <th>Reason</th>
          <th>Date Rejected</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="org in rejectedOrganizations"
          :key="org.organizationsChoiceID"
        >
          <td>{{ org.organization?.organizationName }}</td>
          <td>{{ org.training?.trainingName }}</td>
          <td>{{ org.career?.position }}</td>
          <td>{{ org.rejectionReason }}</td>
          <td>{{ formatDate(org.updated_at) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const rejectedOrganizations = ref([]);
const loading = ref(true);

// Load rejected organizations
async function loadRejectedOrganizations() {
  try {
    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/admin/rejected-organizations"
    );
    rejectedOrganizations.value = res.data;
  } catch (err) {
    console.error("Error loading rejected organizations:", err);
  } finally {
    loading.value = false;
  }
}

function formatDate(date) {
  if (!date) return "-";
  return new Date(date).toLocaleString();
}

onMounted(() => {
  loadRejectedOrganizations();
});
</script>

<style>
.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
.table th,
.table td {
  border: 1px solid #ddd;
  padding: 10px;
}
.title {
  margin-bottom: 20px;
}
</style>