/**
 * Banana Quiz Game - Client-Side Game Controller
 * CIS046-3 Software For Enterprise
 * 
 * Demonstrates:
 * - Theme 2: Interoperability (Fetch API, asynchronous JSON communication with PHP backend)
 * - Theme 3: Event-Driven Programming (DOM event listeners, decoupled event handlers)
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

'use strict';

class BananaGameController {
    constructor() {
        // Cached DOM Elements
        this.dom = {
            hudQuestion: document.getElementById('hud-question'),
            hudTotal: document.getElementById('hud-total'),
            hudScore: document.getElementById('hud-score'),
            hudCorrect: document.getElementById('hud-correct'),
            btnRestartRound: document.getElementById('btn-restart-round'),

            puzzleCard: document.getElementById('puzzle-card'),
            puzzleLoading: document.getElementById('puzzle-loading'),
            puzzleError: document.getElementById('puzzle-error'),
            puzzleErrorMessage: document.getElementById('puzzle-error-message'),
            btnRetryPuzzle: document.getElementById('btn-retry-puzzle'),
            puzzleContent: document.getElementById('puzzle-content'),
            puzzleImg: document.getElementById('puzzle-img'),

            inputControls: document.getElementById('input-controls'),
            answerDisplay: document.getElementById('answer-display'),
            keypad: document.getElementById('keypad'),
            keyButtons: document.querySelectorAll('.key-btn'),
            btnClearAnswer: document.getElementById('btn-clear-answer'),
            btnSubmitAnswer: document.getElementById('btn-submit-answer'),

            feedbackBox: document.getElementById('feedback-box'),
            feedbackText: document.getElementById('feedback-text'),
            btnNextQuestion: document.getElementById('btn-next-question'),

            gameOverCard: document.getElementById('game-over-card'),
            finalScoreVal: document.getElementById('final-score-val'),
            finalAccVal: document.getElementById('final-acc-val'),
            finalCorrectVal: document.getElementById('final-correct-val'),
            btnPlayAgain: document.getElementById('btn-play-again'),
        };

        // Application State
        this.state = {
            selectedDigit: '',
            isSubmitting: false,
            isAwaitingNext: false,
            currentQuestion: 1,
            totalQuestions: 5,
        };

        // Initialize Controller
        this.init();
    }

    /**
     * Bind all event listeners (Demonstrating Theme 3: Event-Driven Programming).
     */
    init() {
        this.bindEvents();
        // Load first puzzle on page entry
        this.fetchPuzzle();
    }

    /**
     * Attach event listeners using addEventListener().
     */
    bindEvents() {
        // Event 1: On-screen keypad digit selection
        this.dom.keyButtons.forEach((btn) => {
            btn.addEventListener('click', (e) => {
                const digit = e.currentTarget.getAttribute('data-val');
                this.handleDigitInput(digit);
            });
        });

        // Event 2: Clear selected digit
        this.dom.btnClearAnswer.addEventListener('click', () => {
            this.handleClearAnswer();
        });

        // Event 3: Submit answer
        this.dom.btnSubmitAnswer.addEventListener('click', () => {
            this.handleSubmitAnswer();
        });

        // Event 4: Next question click
        this.dom.btnNextQuestion.addEventListener('click', () => {
            this.handleNextQuestion();
        });

        // Event 5: External API Retry click
        this.dom.btnRetryPuzzle.addEventListener('click', () => {
            this.fetchPuzzle();
        });

        // Event 6: Restart Round click
        this.dom.btnRestartRound.addEventListener('click', () => {
            this.handleRestartRound();
        });

        // Event 7: Play Again click
        this.dom.btnPlayAgain.addEventListener('click', () => {
            this.handleRestartRound();
        });

        // Event 8: Physical keyboard event listener
        window.addEventListener('keydown', (e) => {
            // Ignore keystrokes if typing inside text fields or game is over
            if (e.target.tagName === 'INPUT' && e.target.id !== 'answer-display') {
                return;
            }

            if (/^[0-9]$/.test(e.key)) {
                this.handleDigitInput(e.key);
            } else if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Escape') {
                this.handleClearAnswer();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.state.isAwaitingNext) {
                    this.handleNextQuestion();
                } else {
                    this.handleSubmitAnswer();
                }
            }
        });
    }

    /**
     * Event Handler: Digit selection
     * @param {string} digit 
     */
    handleDigitInput(digit) {
        if (this.state.isSubmitting || this.state.isAwaitingNext) {
            return;
        }
        this.state.selectedDigit = digit;
        this.dom.answerDisplay.value = digit;

        // Highlight active key button
        this.dom.keyButtons.forEach((btn) => {
            if (btn.getAttribute('data-val') === digit) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    /**
     * Event Handler: Clear Answer
     */
    handleClearAnswer() {
        if (this.state.isSubmitting || this.state.isAwaitingNext) {
            return;
        }
        this.state.selectedDigit = '';
        this.dom.answerDisplay.value = '';
        this.dom.keyButtons.forEach(btn => btn.classList.remove('active'));
    }

    /**
     * Event Handler: Restart Round
     */
    async handleRestartRound() {
        if (!confirm('Are you sure you want to restart this round? Current round progress will be reset.')) {
            return;
        }

        try {
            const baseUrl = getAppBaseUrl();
            await fetch(`${baseUrl}api/reset_game.php`);
            // Reset UI and request fresh puzzle
            this.dom.gameOverCard.style.display = 'none';
            this.dom.puzzleCard.style.display = 'block';
            this.fetchPuzzle(true);
        } catch (err) {
            console.error('Failed to reset game:', err);
        }
    }

    /**
     * Asynchronous Interoperability: Fetch puzzle from PHP backend (Theme 2).
     * @param {boolean} isNewRound 
     */
    async fetchPuzzle(isNewRound = false) {
        this.setLoadingState(true);
        this.resetAnswerState();

        const baseUrl = getAppBaseUrl();
        const url = `${baseUrl}api/get_puzzle.php${isNewRound ? '?new=1' : ''}`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.status === 401) {
                window.location.href = `${baseUrl}auth/login.php`;
                return;
            }

            const data = await response.json();

            if (!response.ok || !data.success) {
                this.showErrorState(data.error || 'Failed to retrieve puzzle from Banana API.');
                return;
            }

            // Check if round is already over
            if (data.is_game_over) {
                this.showGameOver(data);
                return;
            }

            // Update Game State & HUD
            this.updateHud(data.question_number, data.total_questions, data.score, data.correct_count);

            // Render puzzle image cleanly
            this.renderPuzzleImage(data.question_image);

        } catch (error) {
            console.error('[Interoperability Error] fetchPuzzle failed:', error);
            this.showErrorState('Network communication error with server. Please check your connection and retry.');
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Render the puzzle image received from the API.
     * @param {string} imageUrl 
     */
    renderPuzzleImage(imageUrl) {
        this.dom.puzzleImg.style.display = 'none';
        this.dom.puzzleImg.onload = () => {
            this.dom.puzzleImg.style.display = 'block';
        };
        this.dom.puzzleImg.onerror = () => {
            this.showErrorState('Unable to load puzzle image from remote CDN.');
        };
        this.dom.puzzleImg.src = imageUrl;
    }

    /**
     * Asynchronous Interoperability: Submit answer to PHP backend (Theme 2 & 3).
     */
    async handleSubmitAnswer() {
        if (this.state.isSubmitting || this.state.isAwaitingNext) {
            return;
        }

        if (this.state.selectedDigit === '') {
            alert('Please select a numeric answer (0–9) before submitting.');
            return;
        }

        this.state.isSubmitting = true;
        this.dom.btnSubmitAnswer.disabled = true;
        this.dom.btnSubmitAnswer.textContent = 'Verifying...';

        const baseUrl = getAppBaseUrl();
        const csrfToken = getAppCsrfToken();

        try {
            const response = await fetch(`${baseUrl}api/submit_answer.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    answer: this.state.selectedDigit,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                alert(data.error || 'Validation error while submitting answer.');
                this.state.isSubmitting = false;
                this.dom.btnSubmitAnswer.disabled = false;
                this.dom.btnSubmitAnswer.textContent = 'Submit Answer →';
                return;
            }

            // Display Feedback (Theme 3: Event-Driven Feedback)
            this.displayFeedback(data);

        } catch (error) {
            console.error('[Interoperability Error] submitAnswer failed:', error);
            alert('A network communication error occurred. Please try again.');
            this.state.isSubmitting = false;
            this.dom.btnSubmitAnswer.disabled = false;
            this.dom.btnSubmitAnswer.textContent = 'Submit Answer →';
        }
    }

    /**
     * Display feedback for the submitted answer.
     * @param {object} result 
     */
    displayFeedback(result) {
        this.state.isAwaitingNext = true;
        this.state.isSubmitting = false;

        // Disable keypad and input controls while showing answer result
        this.dom.keyButtons.forEach(btn => btn.disabled = true);
        this.dom.btnClearAnswer.disabled = true;
        this.dom.btnSubmitAnswer.style.display = 'none';

        // Update HUD score immediately
        this.dom.hudScore.textContent = result.current_score;
        this.dom.hudCorrect.textContent = result.correct_count;

        this.dom.feedbackBox.className = 'feedback-box ' + (result.is_correct ? 'correct' : 'incorrect');

        if (result.is_correct) {
            this.dom.feedbackText.innerHTML = `
                <div style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.25rem;">🎉 Correct! Fantastic job!</div>
                <div>The correct answer is <strong>${result.correct_solution}</strong>. You earned <strong>+10 points</strong>!</div>
            `;
        } else {
            this.dom.feedbackText.innerHTML = `
                <div style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.25rem;">❌ Not quite!</div>
                <div>You selected <strong>${result.submitted_answer}</strong>, but the correct solution was <strong>${result.correct_solution}</strong>.</div>
            `;
        }

        // Show Next button or transition to Game Over
        if (result.is_game_over) {
            this.dom.btnNextQuestion.textContent = 'See Final Results 🏆';
            this.dom.btnNextQuestion.style.display = 'inline-flex';
            this.dom.btnNextQuestion.onclick = () => {
                this.showGameOver(result);
            };
        } else {
            this.dom.btnNextQuestion.textContent = 'Next Puzzle →';
            this.dom.btnNextQuestion.style.display = 'inline-flex';
            this.dom.btnNextQuestion.onclick = () => {
                this.handleNextQuestion();
            };
        }
    }

    /**
     * Event Handler: Advance to next question
     */
    handleNextQuestion() {
        this.fetchPuzzle();
    }

    /**
     * Display Game Over results card.
     * @param {object} summary 
     */
    showGameOver(summary) {
        this.dom.puzzleCard.style.display = 'none';
        this.dom.gameOverCard.style.display = 'block';

        this.dom.finalScoreVal.textContent = summary.final_score ?? summary.score ?? 0;
        this.dom.finalAccVal.textContent = (summary.accuracy ?? Math.round(((summary.correct_answers ?? summary.correct_count) / summary.total_questions) * 100)) + '%';
        this.dom.finalCorrectVal.textContent = summary.correct_answers ?? summary.correct_count ?? 0;
    }

    /**
     * Update HUD counters.
     */
    updateHud(questionNum, totalNum, score, correct) {
        this.state.currentQuestion = questionNum;
        this.state.totalQuestions = totalNum;

        this.dom.hudQuestion.textContent = questionNum;
        this.dom.hudTotal.textContent = totalNum;
        this.dom.hudScore.textContent = score;
        this.dom.hudCorrect.textContent = correct;
    }

    /**
     * Reset input and feedback state between questions.
     */
    resetAnswerState() {
        this.state.selectedDigit = '';
        this.state.isSubmitting = false;
        this.state.isAwaitingNext = false;

        this.dom.answerDisplay.value = '';
        this.dom.keyButtons.forEach((btn) => {
            btn.disabled = false;
            btn.classList.remove('active');
        });
        this.dom.btnClearAnswer.disabled = false;
        this.dom.btnSubmitAnswer.disabled = false;
        this.dom.btnSubmitAnswer.style.display = 'inline-flex';
        this.dom.btnSubmitAnswer.textContent = 'Submit Answer →';

        this.dom.feedbackBox.className = 'feedback-box';
        this.dom.feedbackBox.style.display = 'none';
        this.dom.btnNextQuestion.style.display = 'none';
    }

    /**
     * Set loading indicator visibility.
     * @param {boolean} isLoading 
     */
    setLoadingState(isLoading) {
        if (isLoading) {
            this.dom.puzzleLoading.style.display = 'block';
            this.dom.puzzleContent.style.display = 'none';
            this.dom.puzzleError.style.display = 'none';
        } else {
            this.dom.puzzleLoading.style.display = 'none';
            this.dom.puzzleContent.style.display = 'block';
        }
    }

    /**
     * Display error panel with retry button.
     * @param {string} message 
     */
    showErrorState(message) {
        this.dom.puzzleLoading.style.display = 'none';
        this.dom.puzzleContent.style.display = 'none';
        this.dom.puzzleError.style.display = 'block';
        this.dom.puzzleErrorMessage.textContent = message;
    }
}

// Instantiate Controller on DOM Content Loaded
document.addEventListener('DOMContentLoaded', () => {
    new BananaGameController();
});
