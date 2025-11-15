<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";
import CalendarSidebar from "@/components/Layout/CalendarSidebar.vue";

// Calendar + events
const events = ref({});
const selectedDate = ref("");
const dayEvents = ref([]);
const calendarRef = ref(null);

function showEvents(dateStr) {
  selectedDate.value = dateStr;
  dayEvents.value = events.value[dateStr] || [];
}

// ✅ Format datetime (with AM/PM)
function formatDateTime(dateStr) {
  if (!dateStr) return "N/A";
  const date = new Date(dateStr);
  if (isNaN(date)) return dateStr;

  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
}

// --- Organizations ---
const organizations = ref([]);
const loading = ref(true);
const error = ref(null);

// API base URL from env
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

// Fetch organizations from backend
async function fetchOrganizations() {
  try {
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/organizations/`
    );

    organizations.value = response.data.map((org) => ({
      id: org.organizationID,
      name: org.name,
      location: org.location || "N/A",
    }));
  } catch (err) {
    console.error("Failed to fetch organizations:", err);
    error.value =
      err.response?.data?.message || "Failed to fetch organizations";
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  await fetchOrganizations();

  const calendar = calendarRef.value;
  const today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD

  if (calendar) {
    calendar.addEventListener("render", () => {
      calendar.querySelectorAll("[data-date]").forEach((el) => {
        const dateStr = el.getAttribute("data-date");
        el.classList.remove("event-day", "today");

        if (events.value[dateStr]) el.classList.add("event-day");
        if (dateStr === today) el.classList.add("today");
      });
    });

    calendar.addEventListener("change", (e) => {
      const pickedDate = e.target.value;
      showEvents(pickedDate);

      calendar
        .querySelectorAll("[data-date]")
        .forEach((el) => el.classList.remove("selected-day"));
      const selectedEl = calendar.querySelector(`[data-date="${pickedDate}"]`);
      if (selectedEl) selectedEl.classList.add("selected-day");
    });

    // Auto-select today's date
    calendar.value = today;
    showEvents(today);

    const event = new Event("change", { bubbles: true });
    calendar.dispatchEvent(event);
  }
});
</script>

<template>
  <div>
    <main class="font-poppins">
      <div class="bg-white m-3 p-4 rounded-lg">
        <!-- Header -->
        <div
          class="sticky top-0 z-10 bg-white pt-4 px-4 pb-2 border-b shadow-sm"
        >
          <h2 class="text-2xl font-bold mb-3">Organizations</h2>
        </div>

        <!-- Loading / Error -->
        <div v-if="loading" class="text-gray-500">Loading organizations...</div>
        <div v-if="error" class="text-red-500">{{ error }}</div>

        <!-- Organization list -->
        <ul v-if="!loading && !error" class="space-y-4">
          <li
            v-for="org in organizations"
            :key="org.id"
            class="p-4 bg-blue-gray rounded-lg relative hover:bg-gray-300 transition cursor-pointer"
          >
            <p class="font-semibold">{{ org.name }}</p>
            <p class="text-gray-600">{{ org.location }}</p>
          </li>
        </ul>
      </div>
    </main>
  </div>
</template>
