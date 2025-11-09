import { defineStore } from "pinia";
import axios from "axios";

export const useTrainingStore = defineStore("trainingStore", {
  state: () => ({
    trainings: [],
    qrCodeValue: null,
    qrExpiresAt: null,
    activeTrainingId: null,
    qrExpireTimeout: null,
  }),
  actions: {
    async fetchTrainings() {
      try {
        const response = await axios.get(import.meta.env.VITE_API_BASE_URL + "/trainings");
        this.trainings = response.data;
        // Schedule QR for each training
        this.trainings.forEach(training => this.scheduleQR(training));
      } catch (error) {
        console.error("Error fetching trainings:", error);
      }
    },

    scheduleQR(training) {
      // Already active?
      if (this.activeTrainingId === training.trainingID && this.qrCodeValue) return;

      const now = new Date();
      const startTime = new Date(training.schedule);
      const endTime = new Date(training.end_time);

      if (now >= startTime && now < endTime) {
        this.generateQR(training);
      } else if (now < startTime) {
        const msUntilStart = startTime - now;
        setTimeout(() => this.generateQR(training), msUntilStart);
        console.log(`QR for "${training.title}" will generate in ${msUntilStart / 1000}s`);
      }
    },

    async generateQR(training) {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.post(
          import.meta.env.VITE_API_BASE_URL + "/trainings/generate-qr",
          { trainingID: training.trainingID },
          { headers: { Authorization: `Bearer ${token}` } }
        );

        this.qrCodeValue =
          import.meta.env.VITE_API_BASE_URL +
          `/attendance/checkin?trainingID=${training.trainingID}&key=${response.data.key}`;

        this.qrExpiresAt = new Date(response.data.expires_at);
        this.activeTrainingId = training.trainingID;

        console.log(`✅ QR Generated for "${training.title}", expires at ${this.qrExpiresAt}`);

        // Clear previous timer
        if (this.qrExpireTimeout) clearTimeout(this.qrExpireTimeout);

        const now = new Date();
        const msUntilExpire = this.qrExpiresAt - now;

        if (msUntilExpire > 0) {
          this.qrExpireTimeout = setTimeout(() => {
            this.qrCodeValue = null;
            this.qrExpiresAt = null;
            this.activeTrainingId = null;
            console.log(`QR for "${training.title}" expired.`);
          }, msUntilExpire);
        }
      } catch (error) {
        console.error("QR GENERATION FAILED:", error);
      }
    },

    startPolling() {
      // Poll every 30 seconds to refresh trainings and schedule QR
      this.fetchTrainings(); // initial fetch
      setInterval(() => {
        this.fetchTrainings();
      }, 30000);
    },
  },
});