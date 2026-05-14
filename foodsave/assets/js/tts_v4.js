/**
 * FoodSave — Text-to-Speech Module (Lecture Vocale)
 * Uses Web Speech API for client-side voice reading
 * Language: French
 */

class VoiceReader {
  constructor(options = {}) {
    this.synth = window.speechSynthesis;
    this.utterance = null;
    this.isPlaying = false;
    this.isPaused = false;
    
    // Configuration
    this.config = {
      rate: options.rate || 1,
      pitch: options.pitch || 1,
      volume: options.volume || 1,
      lang: options.lang || 'fr-FR',
      ...options
    };
    
    // UI callback
    this.onPlayStart = options.onPlayStart || (() => {});
    this.onPlayEnd = options.onPlayEnd || (() => {});
    this.onError = options.onError || (() => {});
  }

  /**
   * Speak the given text
   */
  speak(text) {
    if (!text || !text.trim()) {
      this.onError('Aucun texte à lire.');
      return;
    }

    // Cancel any ongoing speech
    if (this.synth.speaking) {
      this.synth.cancel();
    }

    this.utterance = new SpeechSynthesisUtterance(text);
    this.utterance.lang = this.config.lang;
    this.utterance.rate = this.config.rate;
    this.utterance.pitch = this.config.pitch;
    this.utterance.volume = this.config.volume;

    // Event handlers
    this.utterance.onstart = () => {
      this.isPlaying = true;
      this.isPaused = false;
      this.onPlayStart();
    };

    this.utterance.onend = () => {
      this.isPlaying = false;
      this.isPaused = false;
      this.onPlayEnd();
    };

    this.utterance.onerror = (event) => {
      this.isPlaying = false;
      this.isPaused = false;
      this.onError(`Erreur vocale: ${event.error}`);
    };

    this.synth.speak(this.utterance);
  }

  /**
   * Pause current speech
   */
  pause() {
    if (this.synth.speaking && !this.synth.paused) {
      this.synth.pause();
      this.isPaused = true;
    }
  }

  /**
   * Resume paused speech
   */
  resume() {
    if (this.synth.paused) {
      this.synth.resume();
      this.isPaused = false;
    }
  }

  /**
   * Stop and cancel speech
   */
  stop() {
    this.synth.cancel();
    this.isPlaying = false;
    this.isPaused = false;
    this.onPlayEnd();
  }

  /**
   * Check if browser supports Web Speech API
   */
  static isSupported() {
    return 'speechSynthesis' in window;
  }

  /**
   * Get available voices
   */
  getVoices() {
    return this.synth.getVoices();
  }

  /**
   * Set voice by language
   */
  setVoiceByLang(lang = 'fr-FR') {
    const voices = this.synth.getVoices();
    const voice = voices.find(v => v.lang.startsWith(lang.split('-')[0])) || voices[0];
    if (voice && this.utterance) {
      this.utterance.voice = voice;
    }
  }
}

/**
 * Helper function to create and manage a voice button
 */
function createVoiceButton(elementOrText, options = {}) {
  if (!VoiceReader.isSupported()) {
    console.warn('Web Speech API not supported in this browser');
    return null;
  }

  const container = document.createElement('div');
  container.className = 'voice-button-container';

  const button = document.createElement('button');
  button.className = 'voice-button';
  button.innerHTML = '🔊 Écouter';
  button.title = 'Lire à haute voix';
  button.type = 'button';

  const statusText = document.createElement('span');
  statusText.className = 'voice-status';
  statusText.style.display = 'none';

  // Determine text to read
  let textToRead = '';
  if (typeof elementOrText === 'string') {
    textToRead = elementOrText;
  } else if (elementOrText instanceof HTMLElement) {
    textToRead = elementOrText.innerText || elementOrText.textContent;
  }

  // Create reader instance
  const reader = new VoiceReader({
    lang: options.lang || 'fr-FR',
    rate: options.rate || 1,
    pitch: options.pitch || 1,
    volume: options.volume || 1,
    onPlayStart: () => {
      button.innerHTML = '⏸️ Pause';
      statusText.textContent = 'En cours de lecture...';
      statusText.style.display = 'inline';
    },
    onPlayEnd: () => {
      button.innerHTML = '🔊 Écouter';
      statusText.textContent = '';
      statusText.style.display = 'none';
    },
    onError: (msg) => {
      button.innerHTML = '🔊 Écouter';
      statusText.textContent = msg;
      statusText.className = 'voice-status error';
      statusText.style.display = 'inline';
    }
  });

  // Button click handler
  button.addEventListener('click', (e) => {
    e.preventDefault();
    if (reader.isPlaying) {
      reader.stop();
    } else {
      reader.speak(textToRead);
    }
  });

  container.appendChild(button);
  container.appendChild(statusText);

  // Store reader reference on container
  container.reader = reader;

  return container;
}

/**
 * Global helper: Read multiple elements in sequence
 */
function readMultipleElements(elements, options = {}) {
  if (!VoiceReader.isSupported()) {
    console.warn('Web Speech API not supported');
    return;
  }

  const texts = elements
    .map(el => (el instanceof HTMLElement ? el.innerText : el))
    .filter(text => text && text.trim())
    .join('. ');

  const reader = new VoiceReader(options);
  reader.speak(texts);
  return reader;
}
