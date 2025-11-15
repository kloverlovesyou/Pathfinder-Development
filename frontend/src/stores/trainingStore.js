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

        this.trainings = response.data;

        // Auto generate QR for ongoing trainings
        this.autoGenerateQRs();
      } catch (error) {
        console.error("Error fetching trainings:", error);
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
          value:
            import.meta.env.VITE_API_BASE_URL +
            `/attendance/checkin?trainingID=${training.trainingID}&key=${response.data.key}`,
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
        const startTime = new Date(training.schedule);
        const endTime = new Date(training.end_time);
        const now = new Date();

        if (now >= startTime && now < endTime) {
          if (
            !this.qrCodes[training.trainingID] ||
            new Date(this.qrCodes[training.trainingID].expires_at) < now
          ) {
            this.generateQR(training); // no need for isPublic flag
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