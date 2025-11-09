// src/composables/useTrainingQR.js
import { ref } from "vue";
import axios from "axios";

export const activeTrainingQR = ref(null);   // QR code URL
export const qrExpiresAt = ref(null);        // QR expiry time
export const activeTrainingId = ref(null);   // Training ID with active QR
let qrExpireTimeout = null;                  // Timeout reference

/**
 * Generate a QR code for a training
 */
export async function generateQR(training) {
  try {
    const token = localStorage.getItem("token");
    if (!token) throw new Error("No token found");

    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/trainings/generate-qr",
      { trainingID: training.trainingID },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    activeTrainingQR.value =
      import.meta.env.VITE_API_BASE_URL +
      `/attendance/checkin?trainingID=${training.trainingID}&key=${response.data.key}`;
    qrExpiresAt.value = new Date(response.data.expires_at);
    activeTrainingId.value = training.trainingID;

    // Clear previous timer
    if (qrExpireTimeout) clearTimeout(qrExpireTimeout);

    const now = new Date();
    const msUntilExpire = qrExpiresAt.value - now;

    if (msUntilExpire > 0) {
      qrExpireTimeout = setTimeout(() => {
        activeTrainingQR.value = null;
        qrExpiresAt.value = null;
        activeTrainingId.value = null;
        console.log(`QR for "${training.title}" expired.`);
      }, msUntilExpire);
    }

    console.log(`✅ QR Generated for "${training.title}", expires at ${qrExpiresAt.value}`);
  } catch (error) {
    console.error("QR GENERATION FAILED:", error);
  }
}

/**
 * Schedule a QR code for a training based on start time
 */
export function scheduleQR(training) {
  const now = new Date();
  const startTime = new Date(training.schedule);
  const endTime = new Date(training.end_time);

  // Already started? Generate immediately
  if (now >= startTime && now < endTime) {
    generateQR(training);
  }
  // If training hasn't started, schedule generation
  else if (now < startTime) {
    const msUntilStart = startTime - now;
    setTimeout(() => generateQR(training), msUntilStart);
    console.log(`QR for "${training.title}" will generate in ${msUntilStart / 1000}s`);
  }

  // Clear expired QR if necessary
  if (qrExpiresAt.value && now >= qrExpiresAt.value) {
    activeTrainingQR.value = null;
    qrExpiresAt.value = null;
    activeTrainingId.value = null;
  }
}