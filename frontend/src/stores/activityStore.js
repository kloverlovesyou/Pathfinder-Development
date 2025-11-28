import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";

export const useActivityStore = defineStore("activityStore", () => {
  const upcomingCount = ref(0);
  const completedCount = ref(0);
  const loading = ref(false);
  const lastFetchedAt = ref(null);

  async function fetchCounts() {
    const token = localStorage.getItem("token");
    const savedUser = localStorage.getItem("user");
    if (!token || !savedUser) {
      upcomingCount.value = 0;
      completedCount.value = 0;
      return;
    }

    const user = JSON.parse(savedUser);
    if (!user?.applicantID) {
      upcomingCount.value = 0;
      completedCount.value = 0;
      return;
    }

    try {
      loading.value = true;
      const headers = { Authorization: `Bearer ${token}` };
      const baseUrl = import.meta.env.VITE_API_BASE_URL;

      const [registrationsResult, applicationsResult] = await Promise.allSettled([
        axios.get(`${baseUrl}/registrations`, { headers }),
        axios.get(`${baseUrl}/applications`, { headers }),
      ]);

      if (registrationsResult.status === "fulfilled") {
        const registrations = Array.isArray(registrationsResult.value?.data)
          ? registrationsResult.value.data
          : [];
        upcomingCount.value = registrations.length;
      } else {
        console.error(
          "Failed to fetch registrations for activity counts:",
          registrationsResult.reason
        );
        upcomingCount.value = 0;
      }

      if (applicationsResult.status === "fulfilled") {
        const applications = Array.isArray(applicationsResult.value?.data)
          ? applicationsResult.value.data
          : [];
        completedCount.value = applications.length;
      } else {
        console.error(
          "Failed to fetch applications for activity counts:",
          applicationsResult.reason
        );
        completedCount.value = 0;
      }

      lastFetchedAt.value = new Date();
    } catch (error) {
      console.error("Failed to fetch activity counts:", error);
      upcomingCount.value = 0;
      completedCount.value = 0;
    } finally {
      loading.value = false;
    }
  }

  function setCounts(upcoming = 0, completed = 0) {
    upcomingCount.value = upcoming;
    completedCount.value = completed;
    lastFetchedAt.value = new Date();
  }

  return {
    upcomingCount,
    completedCount,
    loading,
    lastFetchedAt,
    fetchCounts,
    setCounts,
  };
});

