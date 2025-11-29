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

      let trainingCompleted = 0;
      let trainingUpcoming = 0;
      let careerCompleted = 0;
      let careerUpcoming = 0;

      if (registrationsResult.status === "fulfilled") {
        const registrations = Array.isArray(registrationsResult.value?.data)
          ? registrationsResult.value.data
          : [];
        
        console.log("📊 Training registrations fetched:", registrations.length);
        
        // Count training events: completed when status is "completed" or "attended"
        // Note: API returns 'registrationStatus' field
        registrations.forEach((reg) => {
          const status = (reg.registrationStatus || reg.registration_status || reg.status || "").toLowerCase();
          console.log("📚 Training registration status:", status, "Full reg:", reg);
          if (status.includes("completed") || status.includes("attended")) {
            trainingCompleted++;
          } else {
            trainingUpcoming++;
          }
        });
        
        console.log("📚 Training counts - Upcoming:", trainingUpcoming, "Completed:", trainingCompleted);
      } else {
        console.error(
          "Failed to fetch registrations for activity counts:",
          registrationsResult.reason
        );
      }

      if (applicationsResult.status === "fulfilled") {
        const applications = Array.isArray(applicationsResult.value?.data)
          ? applicationsResult.value.data
          : [];
        
        console.log("📊 Career applications fetched:", applications.length);
        
        // Count career events: completed when status is "hired" OR "declined"
        // Note: API returns 'status' field, not 'applicationStatus'
        applications.forEach((app) => {
          const status = (app.status || app.applicationStatus || app.application_status || "").toLowerCase();
          console.log("💼 Career application status:", status, "Full app:", app);
          if (status.includes("hired") || status.includes("declined") || status.includes("decline") || status.includes("reject")) {
            careerCompleted++;
          } else {
            careerUpcoming++;
          }
        });
        
        console.log("💼 Career counts - Upcoming:", careerUpcoming, "Completed:", careerCompleted);
      } else {
        console.error(
          "Failed to fetch applications for activity counts:",
          applicationsResult.reason
        );
      }

      // Set counts: upcoming = all non-completed, completed = all completed
      upcomingCount.value = trainingUpcoming + careerUpcoming;
      completedCount.value = trainingCompleted + careerCompleted;
      
      console.log("✅ Final counts - Upcoming:", upcomingCount.value, "Completed:", completedCount.value);

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

