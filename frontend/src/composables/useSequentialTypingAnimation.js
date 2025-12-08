import { ref, onMounted, onUnmounted } from 'vue';

export function useSequentialTypingAnimation(heading, paragraph, options = {}) {
  const {
    typingSpeed = 100, // milliseconds per character
    deletingSpeed = 50, // milliseconds per character when deleting
    pauseAfterHeading = 800, // pause after heading completes typing (ms)
    pauseAfterParagraph = 2000, // pause after paragraph completes typing (ms)
    pauseAfterDelete = 500, // pause after deleting complete (ms)
    autoStart = false, // whether to start automatically on mount
  } = options;

  const headingText = ref('');
  const paragraphText = ref('');
  const isTyping = ref(true);
  const isRunning = ref(false);
  const phase = ref('heading'); // 'heading', 'paragraph', 'deleting-paragraph', 'deleting-heading'
  
  let timeoutId = null;
  let headingIndex = 0;
  let paragraphIndex = 0;

  const typeHeading = () => {
    if (headingIndex < heading.length) {
      headingText.value = heading.substring(0, headingIndex + 1);
      headingIndex++;
      timeoutId = setTimeout(typeHeading, typingSpeed);
    } else {
      // Heading complete, wait then start typing paragraph
      phase.value = 'paragraph';
      timeoutId = setTimeout(() => {
        typeParagraph();
      }, pauseAfterHeading);
    }
  };

  const typeParagraph = () => {
    if (paragraphIndex < paragraph.length) {
      paragraphText.value = paragraph.substring(0, paragraphIndex + 1);
      paragraphIndex++;
      timeoutId = setTimeout(typeParagraph, typingSpeed);
    } else {
      // Paragraph complete, wait then start deleting paragraph first
      phase.value = 'deleting-paragraph';
      isTyping.value = false;
      timeoutId = setTimeout(() => {
        deleteParagraph();
      }, pauseAfterParagraph);
    }
  };

  const deleteParagraph = () => {
    if (paragraphIndex > 0) {
      paragraphIndex--;
      paragraphText.value = paragraph.substring(0, paragraphIndex);
      timeoutId = setTimeout(deleteParagraph, deletingSpeed);
    } else {
      // Paragraph deleted, now delete heading
      paragraphText.value = '';
      phase.value = 'deleting-heading';
      timeoutId = setTimeout(() => {
        deleteHeading();
      }, 200);
    }
  };

  const deleteHeading = () => {
    if (headingIndex > 0) {
      headingIndex--;
      headingText.value = heading.substring(0, headingIndex);
      timeoutId = setTimeout(deleteHeading, deletingSpeed);
    } else {
      // Everything deleted, wait then start typing again
      headingText.value = '';
      paragraphText.value = '';
      phase.value = 'heading';
      timeoutId = setTimeout(() => {
        isTyping.value = true;
        typeHeading();
      }, pauseAfterDelete);
    }
  };

  const start = () => {
    stop(); // Stop any existing animation first
    headingIndex = 0;
    paragraphIndex = 0;
    headingText.value = '';
    paragraphText.value = '';
    isTyping.value = true;
    isRunning.value = true;
    phase.value = 'heading';
    typeHeading();
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
    headingText,
    paragraphText,
    isTyping,
    isRunning,
    phase,
    start,
    stop,
  };
}

