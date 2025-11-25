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
    if (!user?.applicantID) return;

    try {
      loading.value = true;
      const { data } = await axios.get(
        `${import.meta.env.VITE_API_BASE_URL}/my-activities/${user.applicantID}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      const activities = data?.activities || [];
      const upcoming = activities.filter((a) =>
        ["upcoming", "registered"].includes(a.status?.toLowerCase())
      ).length;

      const completed = activities.filter(
        (a) => a.status?.toLowerCase() === "completed"
      ).length;

      upcomingCount.value = upcoming;
      completedCount.value = completed;
      lastFetchedAt.value = new Date();
    } catch (error) {
      console.error("Failed to fetch activity counts:", error);
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

