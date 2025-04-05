<div id="livefot-matches" class="livefot-container">
    <div class="livefot-date-nav">
        <button class="prev-date group">&lt;</button>
        <span class="current-date group"></span>
        <button class="next-date group">&gt;</button>
        <div class="date-calendar-wrapper group">
            <button type="button" class="calendar-icon-button group" id="calendar-button">
                <img src="<?php echo plugins_url('assets/images/calendar.svg', dirname(__FILE__)); ?>" 
                     alt="Calendar" 
                     class="calendar-icon vector" 
                     width="20" 
                     height="20">
                <span class="sr-only vector">Open Calendar</span>
            </button>
            <input type="text" id="flatpickr-input" class="vector" />
        </div>
    </div>
    <div class="livefot-matches-list group">
        <!-- Matches will appear here -->
    </div>
</div>

<style>
/* Match Details Tabs Styling */
.match-details-tabs {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 1rem;
}

.tabs-header {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 8px 8px 0 0;
}

.tab-button {
    flex: 1;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #6b7280;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.tab-button:hover {
    color: #4b5563;
    background: rgba(255,255,255,0.5);
}

.tab-button.active {
    color: #1f2937;
    background: #fff;
    border-bottom: 2px solid #3b82f6;
}

.tab-button svg {
    width: 18px;
    height: 18px;
}

.tab-content {
    padding: 1.5rem;
    display: none;
}

.tab-content.active {
    display: block;
}

/* Events Tab */
.events-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.event-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.event-time {
    min-width: 50px;
    font-weight: 600;
}

/* .event-icon {
    margin: 0 0.75rem;
} */

/* Stats Tab */
.stats-comparison {
    display: grid;
    gap: 1rem;
}

.stat-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 1rem;
}

.stat-bar {
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
    position: relative;
}

/* Lineup Tab */
.lineup-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-top: 1rem;
}

.team-lineup {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
}

.player-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
}

.player-number {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 50%;
    font-weight: 600;
}
</style>