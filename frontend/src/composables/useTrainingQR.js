// src/composables/useTrainingQR.js
import { ref } from "vue";
import axios from "axios";

export const activeTrainingQR = ref(null);   // QR code URL
export const qrExpiresAt = ref(null);        // QR expiry time
export const activeTrainingId = ref(null);   // Training ID with active QR
export const activeScheduleId = ref(null);   // Schedule ID with active QR
let qrExpireTimeout = null;                  // Timeout reference

/**
 * Generate a QR code for a training or a specific schedule
 */
export async function generateQR(training, schedule = null) {
  try {
    const token = localStorage.getItem("token");
    if (!token) throw new Error("No token found");

    const payload = { trainingID: training.trainingID };
    if (schedule) {
      // Always include trainingScheduleID if it exists
      if (schedule.trainingScheduleID) {
        payload.trainingScheduleID = schedule.trainingScheduleID;
      } else {
        console.warn("Schedule object missing trainingScheduleID:", schedule);
      }
    }

    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/trainings/generate-qr",
      payload,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    activeTrainingQR.value = response.data.attendance_link || (
      import.meta.env.VITE_API_BASE_URL +
      `/attendance/checkin?trainingID=${training.trainingID}&key=${response.data.key}`
    );
    qrExpiresAt.value = new Date(response.data.expires_at);
    activeTrainingId.value = training.trainingID;
    activeScheduleId.value = response.data.trainingScheduleID || (schedule?.trainingScheduleID || null);

    // Clear previous timer
    if (qrExpireTimeout) clearTimeout(qrExpireTimeout);

    const now = new Date();
    const msUntilExpire = qrExpiresAt.value - now;

    if (msUntilExpire > 0) {
      qrExpireTimeout = setTimeout(() => {
        activeTrainingQR.value = null;
        qrExpiresAt.value = null;
        activeTrainingId.value = null;
        activeScheduleId.value = null;
        console.log(`QR for "${training.title}" expired.`);
      }, msUntilExpire);
    }

    const scheduleInfo = schedule ? ` (Schedule: ${schedule.trainingScheduleID})` : '';
    console.log(`✅ QR Generated for "${training.title}"${scheduleInfo}, expires at ${qrExpiresAt.value}`);
    return { success: true, data: response.data };
  } catch (error) {
    console.error("QR GENERATION FAILED:", error);
    return { 
      success: false, 
      error: error.response?.data?.message || error.message || "Failed to generate QR code" 
    };
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