import { defineStore } from "pinia";
import axios from "axios";

export const useTrainingStore = defineStore("trainingStore", {
  state: () => ({
    trainings: [],
    qrCodes: {},       // { trainingID: { value, expires_at } }
    activeTrainingId: null,
    qrExpireTimeouts: {}, // per-training timeout
  }),

  actions: {
    async fetchTrainings() {
      try {
        const response = await axios.get(
          import.meta.env.VITE_API_BASE_URL + "/trainings"
        );

        console.log("Trainings API response:", response.data);
        this.trainings = Array.isArray(response.data) ? response.data : [];

        // Auto generate QR for ongoing trainings
        this.autoGenerateQRs();
      } catch (error) {
        console.error("Error fetching trainings:", error);
        console.error("Error details:", error.response?.data || error.message);
        this.trainings = []; // Set to empty array on error
      }
    },

  async generateQR(training) {
    try {
      const token = localStorage.getItem("token");
      const headers = token ? { Authorization: `Bearer ${token}` } : {};

      const response = await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/trainings/generate-qr",
        { trainingID: training.trainingID },
        { headers } // headers will be empty for public users
      );

      this.qrCodes = {
      ...this.qrCodes,
      [training.trainingID]: {
        value: `${import.meta.env.VITE_FRONTEND_URL}/attendance/checkin?trainingID=${training.trainingID}&key=${response.data.key}`,
        expires_at: new Date(response.data.expires_at),
      },
    };  
    } catch (error) {
      console.error("QR GENERATION FAILED:", error.response?.data || error);
    }
  },

    autoGenerateQRs() {
      const now = new Date();

      this.trainings.forEach((training) => {
        // Check all schedules for the training
        if (training.schedules && training.schedules.length > 0) {
          training.schedules.forEach((schedule) => {
            const startTime = new Date(schedule.schedule);
            const endTime = new Date(schedule.end_time);
            
            if (now >= startTime && now < endTime) {
              if (
                !this.qrCodes[training.trainingID] ||
                new Date(this.qrCodes[training.trainingID].expires_at) < now
              ) {
                this.generateQR(training);
              }
            }
          });
        } else {
          // Fallback to first schedule fields for backward compatibility
          const startTime = training.schedule ? new Date(training.schedule) : null;
          const endTime = training.end_time ? new Date(training.end_time) : null;
          
          if (startTime && endTime && now >= startTime && now < endTime) {
            if (
              !this.qrCodes[training.trainingID] ||
              new Date(this.qrCodes[training.trainingID].expires_at) < now
            ) {
              this.generateQR(training);
            }
          }
        }
      });
    },

    startPolling() {
      this.fetchTrainings(); // initial load
      setInterval(() => this.fetchTrainings(), 30000); // every 30 sec
    },
  },
});