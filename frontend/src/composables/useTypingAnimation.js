import { ref, onMounted, onUnmounted } from 'vue';

export function useTypingAnimation(text, options = {}) {
  const {
    typingSpeed = 100, // milliseconds per character
    deletingSpeed = 50, // milliseconds per character when deleting
    pauseDuration = 2000, // pause after typing complete (ms)
    pauseAfterDelete = 500, // pause after deleting complete (ms)
    autoStart = false, // whether to start automatically on mount
    onTypingComplete = null, // callback when typing completes
  } = options;

  const displayedText = ref('');
  const isTyping = ref(true);
  const isRunning = ref(false);
  let timeoutId = null;
  let currentIndex = 0;

  const typeText = () => {
    if (currentIndex < text.length) {
      displayedText.value = text.substring(0, currentIndex + 1);
      currentIndex++;
      timeoutId = setTimeout(typeText, typingSpeed);
    } else {
      // Finished typing, call callback if provided
      if (onTypingComplete) {
        onTypingComplete();
      }
      // Wait then start deleting
      isTyping.value = false;
      timeoutId = setTimeout(() => {
        deleteText();
      }, pauseDuration);
    }
  };

  const deleteText = () => {
    if (currentIndex > 0) {
      currentIndex--;
      displayedText.value = text.substring(0, currentIndex);
      timeoutId = setTimeout(deleteText, deletingSpeed);
    } else {
      // Finished deleting, wait then start typing again
      displayedText.value = '';
      timeoutId = setTimeout(() => {
        isTyping.value = true;
        typeText();
      }, pauseAfterDelete);
    }
  };

  const start = () => {
    stop(); // Stop any existing animation first
    currentIndex = 0;
    displayedText.value = '';
    isTyping.value = true;
    isRunning.value = true;
    typeText();
  };

  const stop = () => {
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
    isRunning.value = false;
  };

  onMounted(() => {
    if (autoStart) {
      start();
    }
  });

  onUnmounted(() => {
    stop();
  });

  return {
    displayedText,
    isTyping,
    isRunning,
    start,
    stop,
  };
}

