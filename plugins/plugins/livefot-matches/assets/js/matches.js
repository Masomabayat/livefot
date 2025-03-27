(function ($) {
    'use strict';
    window.needPrefetchAdjacentDates = true;
    window.ajaxRequests = [];
    // ========== CONFIGURATIONS & CONSTANTS ==========

    const iconBase = livefotAjax.icons_base_url;

    // Define icons globally for the module
    const icons = {
        events: [
            '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>',
            '<polyline points="14 2 14 8 20 8"></polyline>',
            '<line x1="16" y1="13" x2="8" y2="13"></line>',
            '<line x1="16" y1="17" x2="8" y2="17"></line>',
            '<line x1="10" y1="9" x2="8" y2="9"></line>'
        ].join(''),
        stats: [
            '<line x1="18" y1="20" x2="18" y2="10"></line>',
            '<line x1="12" y1="20" x2="12" y2="4"></line>',
            '<line x1="6" y1="20" x2="6" y2="14"></line>'
        ].join(''),
        lineup: [
            '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>',
            '<circle cx="9" cy="7" r="4"></circle>',
            '<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>',
            '<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>'
        ].join('')
    };

    // Use intervals from localized data
    const INTERVALS = livefotAjax.intervals || {
        matches: 30000,    // fallback 30s for live matches
        lineups: 60000,    // fallback 60s
        events: 60000,     // fallback 60s
        statistics: 120000 // fallback 120s
    };

    const CACHE_CONFIG = {
        LIVE_MATCHES: INTERVALS.matches,
        FUTURE_MATCHES: 2 * 60 * 60 * 1000,
        PAST_MATCHES: 2 * 60 * 60 * 1000
    };

    const MATCH_STATUSES = {
        LIVE: 'LIVE',
        HT: 'HT',
        ET: 'ET',
        PEN_LIVE: 'PEN_LIVE',
        PEN: 'PEN',
        FT_PEN: 'FT_PEN',
        BREAK: 'BREAK',
        INT: 'INT',
        FT: 'FT',
        NS: 'NS',
        AET: 'AET',
        POSTPONED: 'POSTPONED'
    };

    // ========== CACHE MANAGERS ==========

    // --- Matches Cache ---
    function MatchesCache() {
        this.cache = new Map();
    }
    MatchesCache.prototype = {
        getDateKey: function (date) {
            return date.toISOString().split('T')[0];
        },
        getCacheDuration: function (matchDate) {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            if (matchDate < yesterday) {
                return CACHE_CONFIG.PAST_MATCHES;
            } else if (matchDate > today) {
                return CACHE_CONFIG.FUTURE_MATCHES;
            }
            return CACHE_CONFIG.LIVE_MATCHES;
        },
        isValidCache: function (cacheEntry) {
            if (!cacheEntry) return false;
            const now = Date.now();
            return (now - cacheEntry.timestamp) < cacheEntry.duration;
        },
        set: function (date, data) {
            // date = new Date(jQuery(".flatpickr-input").val());
            console.log(date);
            const dateKey = this.getDateKey(date);
            // console.log("MatchesCache set called", dateKey, [date]);
            const cacheDuration = this.getCacheDuration(date);
            this.cache.set(dateKey, {
                data: data,
                timestamp: Date.now(),
                duration: cacheDuration
            });
            if (window.needPrefetchAdjacentDates) {
                this.prefetchAdjacentDates(date);
            }
        },
        get: function (date) {
            // date = new Date(jQuery(".flatpickr-input").val());
            // console.log(date);
            // const dateKey = this.getDateKey(date);
            const cacheEntry = this.cache.get(date);
            return this.isValidCache(cacheEntry) ? cacheEntry.data : null;
        },
        prefetchAdjacentDates: function (currentDate) {
            window.needPrefetchAdjacentDates = false;
            const self = this;
            const dates = [
                new Date(currentDate.getTime() - 86400000), // Previous day
                new Date(currentDate.getTime() + 86400000)  // Next day
            ];
            dates.forEach(async function (date) {
                console.log(date);
                const dateKey = self.getDateKey(date);

                // console.log(date, dateKey, self.cache);
                if (!self.cache.has(dateKey)) {
                    try {
                        const data = await self.fetchMatchData(date);
                        self.set(date, data);
                    } catch (error) {
                        console.error('Error prefetching data:', error);
                    }
                }
            });
        },
        // Function to cancel all pending AJAX requests
        cancelAllRequests: async function () {
            window.ajaxRequests.forEach((req) => req.abort()); // Abort each request
            window.ajaxRequests = []; // Reset the array
        },
        fetchMatchData: async function (date) {
            console.log(date);
            try {
                await this.cancelAllRequests();
            } catch (error) {
                console.log(error);
                
            }
            const dateKey = this.getDateKey(date);
            // console.log('fetchMatchData', dateKey);
            // Use the timezone offset for the selected date instead of the current time.
            const utcOffset = -(date.getTimezoneOffset());
            return $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_matches',
                    nonce: livefotAjax.nonce,
                    date: dateKey,
                    utc_offset: utcOffset
                },
                beforeSend: function(xhr) {
                    window.ajaxRequests.push(xhr); // Store the request in global array
                }
            }).then(function (response) {
                return response.data;
            });
        },
        cleanup: function () {
            for (const [key, value] of this.cache.entries()) {
                if (!this.isValidCache(value)) {
                    this.cache.delete(key);
                }
            }
        }
    };

    // --- Lineup Cache ---
    function LineupCache() {
        this.cache = new Map();
    }
    LineupCache.prototype = {
        isValidCache: function (cacheEntry) {
            if (!cacheEntry) return false;
            return (Date.now() - cacheEntry.timestamp) < INTERVALS.lineups;
        },
        set: function (matchId, data) {
            this.cache.set(matchId, {
                data: data,
                timestamp: Date.now()
            });
        },
        get: function (matchId) {
            const entry = this.cache.get(matchId);
            return this.isValidCache(entry) ? entry.data : null;
        },
        cleanup: function () {
            for (const [key, value] of this.cache.entries()) {
                if (!this.isValidCache(value)) {
                    this.cache.delete(key);
                }
            }
        }
    };

    // --- Events Cache ---
    function EventsCache() {
        this.cache = new Map();
    }
    EventsCache.prototype = {
        isValidCache: function (cacheEntry) {
            if (!cacheEntry) return false;
            return (Date.now() - cacheEntry.timestamp) < INTERVALS.events;
        },
        set: function (matchId, data) {
            this.cache.set(matchId, {
                data: data,
                timestamp: Date.now()
            });
        },
        get: function (matchId) {
            const entry = this.cache.get(matchId);
            return this.isValidCache(entry) ? entry.data : null;
        },
        cleanup: function () {
            for (const [key, value] of this.cache.entries()) {
                if (!this.isValidCache(value)) {
                    this.cache.delete(key);
                }
            }
        }
    };

    // --- Statistics Cache ---
    function StatsCache() {
        this.cache = new Map();
    }
    StatsCache.prototype = {
        isValidCache: function (cacheEntry) {
            if (!cacheEntry) return false;
            // Example: 2 minutes
            const STATS_CACHE_DURATION = 2 * 60 * 1000;
            return (Date.now() - cacheEntry.timestamp) < STATS_CACHE_DURATION;
        },
        set: function (matchId, data) {
            this.cache.set(matchId, {
                data: data,
                timestamp: Date.now()
            });
        },
        get: function (matchId) {
            const entry = this.cache.get(matchId);
            return this.isValidCache(entry) ? entry.data : null;
        },
        cleanup: function () {
            for (const [key, value] of this.cache.entries()) {
                if (!this.isValidCache(value)) {
                    this.cache.delete(key);
                }
            }
        }
    };

    // --- Standings Cache ---
    function StandingsCache() {
        this.cache = new Map();
    }
    StandingsCache.prototype = {
        isValidCache: function (cacheEntry) {
            if (!cacheEntry) return false;
            // Example: 5 minutes
            const STANDINGS_CACHE_DURATION = 5 * 60 * 1000;
            return (Date.now() - cacheEntry.timestamp) < STANDINGS_CACHE_DURATION;
        },
        set: function (key, data) {
            this.cache.set(key, {
                data: data,
                timestamp: Date.now()
            });
        },
        get: function (key) {
            const entry = this.cache.get(key);
            return this.isValidCache(entry) ? entry.data : null;
        },
        cleanup: function () {
            for (const [key, value] of this.cache.entries()) {
                if (!this.isValidCache(value)) {
                    this.cache.delete(key);
                }
            }
        }
    };

    // ========== MATCHES MANAGER ==========

    function MatchesManager() {
        this.cache = new MatchesCache();
        this.lineupCache = new LineupCache();
        this.eventsCache = new EventsCache();
        this.statsCache = new StatsCache();
        this.standingsCache = new StandingsCache();
        this.liveOnlyCache = new MatchesCache();

        this.currentRequest = null;
        let currentDateTemp = new Date();
        this.state = {
            currentDate: new Date(),
            previousDate: new Date(currentDateTemp.getTime() - 86400000), // Previous day
            nextDate: new Date(currentDateTemp.getTime() + 86400000),  // Next day
            showLiveOnly: false,
            allLeaguesExpanded: true,
            liveMatches: {},
            refreshInterval: null,
            lineupRefreshInterval: null,
            eventsRefreshInterval: null,
            statsRefreshInterval: null,
            openEventMatches: {},
            openLineupMatches: {},
            openStandingsMatches: {},
            openStatsMatches: {},
            activePanels: {},
            // We store "important only" preference per match ID for the events filter
            showImportantEventsByMatch: {},
            // overlay refresh interval for the full-screen details view
            overlayRefreshInterval: null,
            overlayTabIntervals: {}
        };

        // The statuses that qualify as "live" for filtering
        this.LIVE_STATUSES = ['LIVE', 'HT', 'INT', 'ET', 'BREAK', 'PEN_LIVE', 'PEN'];
    }

    MatchesManager.prototype = {

        // ---------- INIT ----------
        init: function () {
            this.bindEvents();
            this.initDatePicker();
            this.updateDateDisplay();
            this.loadMatches();
            this.setupRefreshInterval();
            // this.setupLineupRefresh();
            // this.setupEventsRefresh();
            // this.setupStatsRefresh();
            this.setupStandingsCleanup();
            this.setupPageVisibilityHandling();
        },

        // ---------- DATE PICKER ----------
        initDatePicker: function () {
            const self = this;
            const dateInput = $('#flatpickr-input');
            const calendarIcon = $('.calendar-icon');
            const dateFormat = "Y-m-d";

            const flatpickrConfig = {
                dateFormat: dateFormat,
                defaultDate: self.state.currentDate,
                locale: {
                    firstDayOfWeek: 1
                },
                disableMobile: false,
                allowInput: true,
                clickOpens: false,

                onChange: function (selectedDates, dateStr) {
                    if (selectedDates.length > 0) {
                        const selectedDate = new Date(selectedDates[0]);
                        const localDate = new Date(
                            selectedDate.getFullYear(),
                            selectedDate.getMonth(),
                            selectedDate.getDate(),
                            0, 0, 0, 0
                        );
                        self.state.currentDate = localDate;
                        self.updateDateDisplay();
                        self.loadMatches(true);
                        self.setupRefreshInterval();
                        dateInput.attr('value', dateStr);
                    }
                },

                onOpen: function () {
                    calendarIcon.addClass('active');
                    dateInput.attr('aria-expanded', 'true');
                },
                onClose: function () {
                    calendarIcon.removeClass('active');
                    dateInput.attr('aria-expanded', 'false');
                    const inputDate = dateInput.val();
                    if (inputDate && !isNaN(new Date(inputDate))) {
                        const formattedDate = this.formatDate(new Date(inputDate), dateFormat);
                        dateInput.val(formattedDate);
                    } else {
                        dateInput.val(this.formatDate(self.state.currentDate, dateFormat));
                    }
                }
            };

            const fp = dateInput.flatpickr(flatpickrConfig);
            dateInput.data('flatpickr', fp);

            dateInput
                .attr('role', 'combobox')
                .attr('aria-autocomplete', 'none')
                .attr('aria-expanded', 'false')
                .attr('aria-label', 'Select date');

            $('#calendar-button').on('click', function (e) {
                e.preventDefault();
                const fpInstance = dateInput.data('flatpickr');
                if (fpInstance) {
                    if (fpInstance.isOpen) {
                        fpInstance.close();
                    } else {
                        fpInstance.open();
                        const button = $(this);
                        const buttonRect = button[0].getBoundingClientRect();
                        const calendarElement = fpInstance.calendarContainer;
                        $(calendarElement).css({
                            top: buttonRect.bottom + window.scrollY + 5 + 'px',
                            left: buttonRect.left + window.scrollX + 'px'
                        });
                    }
                }
            }).attr('aria-label', 'Open calendar');

            dateInput.on('keydown', function (e) {
                const fpInstance = dateInput.data('flatpickr');
                if (e.key === 'Enter' && fpInstance) {
                    e.preventDefault();
                    if (fpInstance.isOpen) {
                        fpInstance.close();
                    } else {
                        fpInstance.open();
                    }
                }
            });
        },

        // ---------- PAGE VISIBILITY ----------
        setupPageVisibilityHandling: function () {
            const self = this;
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    self.pauseRefreshIntervals();
                } else {
                    self.loadMatches(true);
                    self.setupRefreshInterval();
                    // self.setupLineupRefresh();
                    // self.setupEventsRefresh();
                    // self.setupStatsRefresh();
                }
            });
        },

        pauseRefreshIntervals: function () {
            if (this.state.refreshInterval) {
                clearInterval(this.state.refreshInterval);
                this.state.refreshInterval = null;
            }
            if (this.state.lineupRefreshInterval) {
                clearInterval(this.state.lineupRefreshInterval);
                this.state.lineupRefreshInterval = null;
            }
            if (this.state.eventsRefreshInterval) {
                clearInterval(this.state.eventsRefreshInterval);
                this.state.eventsRefreshInterval = null;
            }
            if (this.state.statsRefreshInterval) {
                clearInterval(this.state.statsRefreshInterval);
                this.state.statsRefreshInterval = null;
            }
            if (this.currentRequest) {
                this.currentRequest.abort();
                this.currentRequest = null;
            }
        },

        // ========== RENDERING ==========

        renderMatches: function (leagues) {
            console.log("leagues", leagues);
            
            if (!leagues) return;
            const self = this;
            const $container = $('.livefot-matches-list');

            let content = this.renderGlobalControls();
            if (leagues.length === 0) {
                content += '<div class="no-matches">No matches found for this date</div>';
            } else {
                leagues.forEach(function (league) {
                    const filteredFixtures = self.state.showLiveOnly
                        ? league.fixtures.filter(function (match) {
                            return self.LIVE_STATUSES.indexOf(match.time.status) !== -1;
                        })
                        : league.fixtures;

                    if (filteredFixtures.length > 0) {
                        content += self.renderLeagueSection(league, filteredFixtures);
                    }
                });
            }
            $container.html(content);
        },

        renderFromCache: function () {
            const self = this;
            let leaguesArray = [];

            if (this.state.showLiveOnly) {
                // gather all live matches from the entire cache
                this.cache.cache.forEach(function (cacheEntry) {
                    cacheEntry.data.forEach(function (league) {
                        const liveFixtures = league.fixtures.filter(function (match) {
                            return self.LIVE_STATUSES.indexOf(match.time.status) !== -1;
                        });
                        if (liveFixtures.length > 0) {
                            leaguesArray.push({
                                league_info: league.league_info,
                                fixtures: liveFixtures
                            });
                        }
                    });
                });
            } else {
                const cachedData = this.cache.get(this.state.currentDate);
                if (cachedData) {
                    leaguesArray = cachedData;
                }
            }
            this.renderMatches(leaguesArray);
        },

        // ========== MAIN LOAD FUNCTIONS ==========

        loadMatches: function (forceRefresh = false, retryCount = 0, specificDate = null) {
            const self = this;
            const dateToLoad = specificDate || this.state.currentDate;
            const dateKey = dateToLoad.toISOString().split('T')[0];

            // console.log([this.cache, dateToLoad, this.state.currentDate]);
            // console.log(this.cache.get(dateKey));
            // const cachedData = !forceRefresh && this.cache.get(dateToLoad);
            const cachedData = this.cache.get(dateKey);
            console.log(["cachedData", cachedData]);
            
            // If we have valid cached data, render from cache
            if (cachedData) {
                this.renderMatches(cachedData);
                return;
            }

            // Otherwise, fetch from server
            this.cache.fetchMatchData(dateToLoad)
                .then(data => {
                    self.cache.set(dateToLoad, data);
                    self.renderMatches(data);
                })
                .catch(error => {
                    console.error('Error loading matches:', error);
                    if (retryCount < 3) {
                        setTimeout(() => self.loadMatches(forceRefresh, retryCount + 1), 2000);
                    } else {
                        $('.livefot-matches-list').html('<div class="error-message">Unable to load matches. Please try again later.</div>');
                    }
                });
        },

        loadLiveMatches: function (forceRefresh = false, retryCount = 0) {
            const self = this;
            const cachedData = !forceRefresh && this.liveOnlyCache.get(this.state.currentDate);

            if (cachedData) {
                this.updateLiveMatches(cachedData);
                this.renderMatches(cachedData);
                return Promise.resolve(cachedData);
            }

            if (this.currentRequest) {
                this.currentRequest.abort();
            }

            this.currentRequest = $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_live_matches',
                    nonce: livefotAjax.nonce
                },
                dataType: 'json'
            });

            return this.currentRequest
                .then(function (response) {
                    self.currentRequest = null;
                    if (!response.success || !response.data) {
                        throw new Error(response.data || 'No live matches found');
                    }
                    const leaguesArray = response.data;
                    self.liveOnlyCache.set(self.state.currentDate, leaguesArray);
                    self.updateLiveMatches(leaguesArray);
                    self.renderMatches(leaguesArray);
                    return leaguesArray;
                })
                .catch(function (error) {
                    self.currentRequest = null;
                    if (error.statusText === 'abort') {
                        return;
                    }
                    console.error('Error loading live matches:', error);
                    if (retryCount < 3) {
                        return new Promise(resolve => {
                            setTimeout(() => {
                                resolve(self.loadLiveMatches(forceRefresh, retryCount + 1));
                            }, 2000);
                        });
                    } else {
                        self.state.showLiveOnly = false;
                        $('.toggle-live').removeClass('active')
                            .find('span').text('Show Live Only');
                        $('.toggle-live').find('i').attr('class', 'icon-live-inactive');
                        self.renderFromCache();
                        self.loadMatches(true);
                        throw error;
                    }
                });
        },

        updateLiveMatches: function (data) {
            const self = this;
            this.state.liveMatches = {};
            data.forEach(function (league) {
                league.fixtures.forEach(function (match) {
                    if (self.LIVE_STATUSES.indexOf(match.time.status) !== -1) {
                        self.state.liveMatches[match.id] = true;
                    }
                });
            });
        },

        // ---------- REFRESH INTERVALS ----------
        setupRefreshInterval: function () {
            const self = this;
            if (this.state.refreshInterval) {
                clearInterval(this.state.refreshInterval);
            }
            const isToday = this.isToday(this.state.currentDate);

            if (isToday || Object.keys(this.state.liveMatches).length > 0) {
                this.state.refreshInterval = setInterval(function () {
                    if (self.state.showLiveOnly) {
                        self.loadLiveMatches(true)
                            .catch(function () {
                                self.state.showLiveOnly = false;
                                $('.toggle-live').removeClass('active')
                                    .find('span').text('Show Live Only');
                                $('.toggle-live').find('i').attr('class', 'icon-live-inactive');
                                self.renderFromCache();
                                self.loadMatches(true);
                            });
                    } else {
                        self.loadMatches(true);
                    }
                }, INTERVALS.matches);
            }
        },

        isToday: function (date) {
            const today = new Date();
            return date.getFullYear() === today.getFullYear() &&
                date.getMonth() === today.getMonth() &&
                date.getDate() === today.getDate();
        },

        setupLineupRefresh: function () {
            const self = this;
            setInterval(async function () {
                const matchItems = $('.match-item');
                for (let i = 0; i < matchItems.length; i++) {
                    const matchId = $(matchItems[i]).data('match-id');
                    await self.fetchAndCacheLineup(matchId);
                }
            }, INTERVALS.lineups);
        },

        setupEventsRefresh: function () {
            const self = this;
            setInterval(async function () {
                $('.match-item').each(async function () {
                    const matchId = $(this).data('match-id');
                    const status = $(this).data('match-status');
                    if (status !== 'NS' && status !== 'POSTPONED') {
                        await self.fetchAndCacheEvents(matchId);
                    }
                });
            }, INTERVALS.events);
        },

        setupStatsRefresh: function () {
            const self = this;
            setInterval(async function () {
                Object.keys(self.state.openStatsMatches).forEach(async function (matchId) {
                    await self.fetchAndCacheStats(matchId);
                });
            }, INTERVALS.statistics);
        },

        setupStandingsCleanup: function () {
            const self = this;
            setInterval(function () {
                self.standingsCache.cleanup();
            }, 10 * 60 * 1000);
        },

        // ========== NEW LIVE UPDATE METHODS FOR OVERLAY ==========//	
        setupOverlayRefresh: function (matchId) {
            const self = this;
            // Clear any existing refresh interval
            if (this.state.overlayRefreshInterval) {
                clearInterval(this.state.overlayRefreshInterval);
            }
            // Set up a new refresh interval that runs every 10 seconds
            this.state.overlayRefreshInterval = setInterval(function () {
                self.updateOverlayData(matchId);
            }, 10000);

            // Initial update
            this.updateOverlayData(matchId);
        },

        /*updateOverlayData: function(matchId) {
            // Find the match element on the main screen
            const $matchItem = $(`.match-item[data-match-id="${matchId}"]`);
            if (!$matchItem.length) return; // Fallback if not found
        
            // Extract score and minute from the main screen element
            const scoreText = $matchItem.find('.match-score-results').text();
            const matchTimeText = $matchItem.find('.match-time').html();
        
            // Update the overlay UI
            const $overlay = $('#match-details-overlay');
            $overlay.find('.scoreboard-score').text(scoreText);
            $overlay.find('.scoreboard-time').html(matchTimeText);
        }
        ,*/

        updateOverlayData: function (matchId) {
            // Find the match element on the main screen
            const $matchItem = $(`.match-item[data-match-id="${matchId}"]`);
            if (!$matchItem.length) return; // Fallback if not found

            // Extract score text
            const scoreText = $matchItem.find('.match-score-results').text();

            // Clone the .match-time element to extract time parts without altering the DOM
            const $timeClone = $matchItem.find('.match-time').clone();
            // Extract injury and added time texts
            const injuryTimeText = $timeClone.find('span.injury-time').text();
            const addedTimeText = $timeClone.find('span.added-time').text();
            // Remove these spans so we get the base time text
            $timeClone.find('span.injury-time, span.added-time').remove();
            const baseTimeText = $timeClone.text().trim() || '';

            // Optionally adjust formatting if needed (e.g., for "FT PEN")
            const matchTimeFormatted = baseTimeText.includes('FT PEN')
                ? baseTimeText.replace(/(FT PEN)(.+)/, '$1<br>$2')
                : baseTimeText;

            // Build the final scoreboard time HTML:
            // - Injury time is inline with the base time
            // - Added time is on a new line (using <br>)
            let scoreboardTimeHtml = matchTimeFormatted;
            if (injuryTimeText) {
                scoreboardTimeHtml += `<span class="scoreboard-injury-time">${injuryTimeText}</span>`;
            }
            if (addedTimeText) {
                scoreboardTimeHtml += `<br><span class="scoreboard-added-time">${addedTimeText}</span>`;
            }

            // Update the overlay UI with fresh values
            const $overlay = $('#match-details-overlay');
            $overlay.find('.scoreboard-score').text(scoreText);
            $overlay.find('.scoreboard-time').html(scoreboardTimeHtml);
        }
        ,


        // ========== LINEUPS / EVENTS / STATS FETCHING ==========
        fetchAndCacheLineup: async function (matchId) {
            const cachedLineup = this.lineupCache.get(matchId);
            if (cachedLineup) {
                this.state.openLineupMatches[matchId] = cachedLineup;
                return;
            }
            await $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_match_lineup',
                    nonce: livefotAjax.nonce,
                    match_id: matchId
                },
                dataType: 'json'
            }).done((response) => {
                if (response.success !== false && response.data) {
                    this.lineupCache.set(matchId, response.data);
                    this.state.openLineupMatches[matchId] = response.data;
                } else {
                    delete this.state.openLineupMatches[matchId];
                }
            }).fail(() => {
                delete this.state.openLineupMatches[matchId];
            });
        },

        fetchAndCacheEvents: async function (matchId) {
            const cachedEvents = this.eventsCache.get(matchId);
            if (cachedEvents) {
                this.state.openEventMatches[matchId] = cachedEvents;
                return;
            }
            await $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_match_events',
                    nonce: livefotAjax.nonce,
                    match_id: matchId
                },
                dataType: 'json'
            }).done((response) => {
                if (response.success !== false && response.data) {
                    this.eventsCache.set(matchId, response.data);
                    this.state.openEventMatches[matchId] = response.data;
                } else {
                    delete this.state.openEventMatches[matchId];
                }
            }).fail(() => {
                delete this.state.openEventMatches[matchId];
            });
        },

        fetchAndCacheStats: async function (matchId) {
            const self = this;
            await $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_match_stats',
                    nonce: livefotAjax.nonce,
                    match_id: matchId
                },
                dataType: 'json'
            }).done(function (response) {
                if (response.success === true && response.data) {
                    self.statsCache.set(matchId, response.data);
                    self.showStatsData(matchId, response.data);
                } else {
                    self.showStatsData(matchId, null, 'Failed to load statistics.');
                }
            }).fail(function () {
                self.showStatsData(matchId, null, 'Error fetching statistics.');
            });
        },

        showStatsData: function (matchId, statsData, errorMessage) {
            // if you had inline stats, you'd put that logic here
        },

        // ========== BIND EVENTS ==========
        bindEvents: function () {
            const self = this;

            // --- Prev/Next date ---
            $('.prev-date').on('click', function () {
                window.needPrefetchAdjacentDates = true;
                const newDate = new Date(self.state.currentDate);
                newDate.setDate(newDate.getDate() - 1);
                self.state.currentDate = newDate;

                self.state.previousDate = new Date(newDate.getTime() - 86400000);
                self.state.nextDate = new Date(newDate.getTime() + 86400000);
                // console.log(self.state);

                self.updateDateDisplay();
                self.loadMatches();
                self.setupRefreshInterval();
            });
            $('.next-date').on('click', function () {
                window.needPrefetchAdjacentDates = true;
                const newDate = new Date(self.state.currentDate);
                newDate.setDate(newDate.getDate() + 1);
                self.state.currentDate = newDate;

                self.state.previousDate = new Date(newDate.getTime() - 86400000);
                self.state.nextDate = new Date(newDate.getTime() + 86400000);

                // console.log(self.state);

                self.updateDateDisplay();
                self.loadMatches();
                self.setupRefreshInterval();
            });

            // --- League expand/collapse ---
            $(document).on('click', '.league-header', function () {
                const $section = $(this).closest('.league-section');
                const $matchesList = $section.find('.matches-list');
                const $arrow = $(this).find('.toggle-arrow');

                if ($section.hasClass('collapsed')) {
                    $matchesList.slideDown(300);
                    $arrow.css('transform', 'rotate(0deg)');
                    $section.removeClass('collapsed');
                } else {
                    $matchesList.slideUp(300);
                    $arrow.css('transform', 'rotate(-90deg)');
                    $section.addClass('collapsed');
                }
            });

            // --- Global expand/collapse ---
            $(document).on('click', '.toggle-expand-all', function () {
                self.state.allLeaguesExpanded = !self.state.allLeaguesExpanded;
                const $button = $(this);
                const $sections = $('.league-section');
                const $arrows = $('.toggle-arrow');
                if (self.state.allLeaguesExpanded) {
                    $sections.removeClass('collapsed').find('.matches-list').slideDown(300);
                    $arrows.css('transform', 'rotate(0deg)');
                    $button.find('i').attr('class', 'icon-collapse');
                    $button.find('span').text('Collapse All');
                } else {
                    $sections.addClass('collapsed').find('.matches-list').slideUp(300);
                    $arrows.css('transform', 'rotate(-90deg)');
                    $button.find('i').attr('class', 'icon-expand');
                    $button.find('span').text('Expand All');
                }
            });

            // --- Show/Hide live only ---
            $(document).on('click', '.toggle-live', function () {
                self.state.showLiveOnly = !self.state.showLiveOnly;
                $('.toggle-live').toggleClass('active');
                if (self.state.showLiveOnly) {
                    $('.toggle-live').find('span').text('Show All Matches');
                    $('.toggle-live').find('i').attr('class', 'icon-live-active');
                    self.renderFromCache();
                    self.loadLiveMatches(true)
                        .then(function () { })
                        .catch(function () {
                            self.state.showLiveOnly = false;
                            $('.toggle-live').removeClass('active')
                                .find('span').text('Show Live Only');
                            $('.toggle-live').find('i').attr('class', 'icon-live-inactive');
                            self.renderFromCache();
                            self.loadMatches(true);
                        });
                } else {
                    $('.toggle-live').find('span').text('Show Live Only');
                    $('.toggle-live').find('i').attr('class', 'icon-live-inactive');
                    self.renderFromCache();
                    self.loadMatches(true);
                }
            });

            // --- Full Screen Match Details (NEW) ---
            $(document).on('click', '.action-button.match-details, .match-item', function (e) {
                // console.log(e);
                e.preventDefault();
                const matchId = $(this).data('match-id');
                if (!matchId) {
                    matchId = $(this).closest('.match-item').data('match-id');
                }
                self.openMatchDetailsFullscreen(matchId);
            });

            // If you have a clickable bench toggle in the lineup
            $(document).on('click', '.team-logo_name_old, .team-header', function () {
                const $benchSection = $(this).closest('.team-info-block').find('.bench-section');
                $benchSection.slideToggle(300);
                $(this).closest('.team-info-block').find(".toggle-arrow").toggleClass("expanded");

            });

            // Toggling "Important vs. All" events in the overlay
            $(document).on('click', '.toggle-important-events', function () {
                const matchId = $(this).data('match-id');
                const current = !!self.state.showImportantEventsByMatch[matchId];
                self.state.showImportantEventsByMatch[matchId] = !current;

                const $overlay = $('#match-details-overlay');
                self.loadOverlayTabContent('events', matchId, $overlay);
            });
        },




        openMatchDetailsFullscreen: function (matchId) {
            const self = this;
            const $matchItem = $(`.match-item[data-match-id="${matchId}"]`);

            // Get league details directly from the match item data attributes
            const leagueName = $matchItem.data('league-name');
            const leagueCountry = $matchItem.data('league-country');
            const leagueStage = $matchItem.data('league-stage');
            const leagueSubInfo = leagueStage ? `${leagueCountry} - ${leagueStage}` : leagueCountry;

            // Create or update the overlay container
            let $overlay = $('#match-details-overlay');
            if ($overlay.length === 0) {
                $overlay = $(`
      <div id="match-details-overlay" class="match-details-overlay">
        <div class="overlay-content">
          <!-- SCOREBOARD SECTION (header) -->
          <div class="scoreboard-section">
            <div class="scoreboard-header">
              <button class="back-to-matches">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
  				<path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 						.708.708L5.707 7.5z"/>
				</svg> 
                <div class="league-details">
                    <span class="league-name">${leagueName}</span>
                    <span class="league-subinfo">${leagueSubInfo}</span>
                </div>
              </button>
            </div>
            <div class="scoreboard-teams"><!-- Teams & scores will be injected here --></div>
          </div>
          <!-- TAB CONTAINER -->
          <div class="tab-container">
            <div class="tab-content active" data-tab="events">
              <div class="loading">Loading events...</div>
            </div>
            <div class="tab-content" data-tab="stats">
              <div class="loading">Loading statistics...</div>
            </div>
            <div class="tab-content" data-tab="lineup">
              <div class="loading">Loading lineup...</div>
            </div>
            <div class="tab-content" data-tab="standings">
              <div class="loading">Loading standings...</div>
            </div>
          </div>
          <!-- TABS FOOTER -->
          <div class="tabs-footer">
            <button class="tab-button active" data-tab="events">Events</button>
            <button class="tab-button" data-tab="stats">Stats</button>
            <button class="tab-button" data-tab="lineup">Lineup</button>
            <button class="tab-button" data-tab="standings">Standings</button>
         </div>
        </div>
      </div>
        <div class="floating-icon float-back-to-matches">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
            </svg
        </div>
    `).appendTo('body');
            } else {
                $overlay.find('.league-name').text(leagueName);
                $overlay.find('.league-subinfo').text(leagueSubInfo);
            }

            // Show the overlay and prevent background scrolling
            $overlay.show();
            document.body.classList.add('overlay-open');

            // Get team names and logos from the match item
            const localTeamName = $matchItem.find('.team.home .team-name').text() || 'Home Team';
            const visitorTeamName = $matchItem.find('.team.away .team-name').text() || 'Away Team';

            const localTeamLogo =
                $matchItem.find('.match-score .team-logo-container').eq(0)
                    .find('.team-logo').attr('src') || 'https://via.placeholder.com/50';
            const visitorTeamLogo =
                $matchItem.find('.match-score .team-logo-container').eq(1)
                    .find('.team-logo').attr('src') || 'https://via.placeholder.com/50';

            // Use a clone of the .match-time element to extract the parts without affecting the original
            const $timeClone = $matchItem.find('.match-time').clone();
            const injuryTimeText = $timeClone.find('span.injury-time').text();
            const addedTimeText = $timeClone.find('span.added-time').text();
            $timeClone.find('span.injury-time, span.added-time').remove();
            const baseTimeText = $timeClone.text().trim() || '';

            // (Optional) Adjust formatting if needed (e.g. for FT PEN)
            const matchTimeFormatted = baseTimeText.includes('FT PEN')
                ? baseTimeText.replace(/(FT PEN)(.+)/, '$1<br>$2')
                : baseTimeText;

            // Build the scoreboard HTML using the extracted parts
            const scoreboardHtml = `
        <div class="scoreboard-wrapper">
          <div class="scoreboard-team scoreboard-home">
            <img src="${localTeamLogo}" alt="${localTeamName} Logo" class="scoreboard-logo">
            <span class="scoreboard-team-name">${localTeamName}</span>
          </div>

          <div class="scoreboard-info">
            <span class="scoreboard-time">${matchTimeFormatted}</span>
            <div class="scoreboard-score">${$matchItem.find('.match-score-results').text() || ''}</div>
            ${$matchItem.find('.aggregate-score').text() ? `<div class="scoreboard-aggregate">${$matchItem.find('.aggregate-score').text()}</div>` : ''}
          </div>

          <div class="scoreboard-team scoreboard-away">
            <img src="${visitorTeamLogo}" alt="${visitorTeamName} Logo" class="scoreboard-logo">
            <span class="scoreboard-team-name">${visitorTeamName}</span>
          </div>
        </div>
    `;


    // ${injuryTimeText ? `<span class="scoreboard-injury-time">${injuryTimeText}</span>` : ''}
    // ${addedTimeText ? `<span class="scoreboard-added-time">${addedTimeText}</span>` : ''}

            // Inject the scoreboard HTML into the overlay
            $overlay.find('.scoreboard-section .scoreboard-teams').html(scoreboardHtml);

            // Clear previous tab content to avoid flashing old data
            $overlay.find('.tab-content').html('<div class="loading"><img class="rotating-img" src="/wp-content/uploads/2025/03/spinner.png" ></div>');

            // Initialize live update functionality for the overlay
            this.setupOverlayRefresh(matchId);


            $overlay.find('.back-to-matches').off('click').on('click', function () {
                // Clear overlay refresh interval
                if (self.state.overlayRefreshInterval) {
                    clearInterval(self.state.overlayRefreshInterval);
                    self.state.overlayRefreshInterval = null;
                }
                // Clear all overlay tab intervals
                for (let tab in self.state.overlayTabIntervals) {
                    clearInterval(self.state.overlayTabIntervals[tab]);
                }
                self.state.overlayTabIntervals = {};

                $overlay.hide();
                document.body.classList.remove('overlay-open');
            });
			$overlay.find('.float-back-to-matches').off('click').on('click', function () {
                // Clear overlay refresh interval
                if (self.state.overlayRefreshInterval) {
                    clearInterval(self.state.overlayRefreshInterval);
                    self.state.overlayRefreshInterval = null;
                }
                // Clear all overlay tab intervals
                for (let tab in self.state.overlayTabIntervals) {
                    clearInterval(self.state.overlayTabIntervals[tab]);
                }
                self.state.overlayTabIntervals = {};

                $overlay.hide();
                document.body.classList.remove('overlay-open');
            });




            // If match status is NS, remove events tab
            const status = $matchItem.data('match-status');
            if (status === 'NS') {
                $overlay.find('.tab-button[data-tab="events"]').remove();
                $overlay.find('.tab-content[data-tab="events"]').remove();
                $overlay.find('.tab-button').removeClass('active').first().addClass('active');
                $overlay.find('.tab-content').removeClass('active').first().addClass('active');
            }

            // Bind tab switching
            $overlay.find('.tab-button').off('click').on('click', function () {
                const tab = $(this).data('tab');
                $overlay.find('.tab-button').removeClass('active');
                $(this).addClass('active');
                $overlay.find('.tab-content').removeClass('active');
                $overlay.find(`.tab-content[data-tab="${tab}"]`).addClass('active');
                self.loadOverlayTabContent(tab, matchId, $overlay);
            });

            // Default tab loading
            if (status !== 'NS') {
                this.state.showImportantEventsByMatch[matchId] = true;
                this.loadOverlayTabContent('events', matchId, $overlay);
            } else {
                const firstTab = $overlay.find('.tab-button.active').data('tab');
                this.loadOverlayTabContent(firstTab, matchId, $overlay);
            }
            $(".tab-container").on("scroll", function () {
                // console.log("Scrolling...", $(".tab-container").scrollTop());

                if ($(".tab-container").scrollTop() > 50) {
                    $(".scoreboard-section").addClass("scrolled");
                } else {
                    $(".scoreboard-section").removeClass("scrolled");
                }
            });
        },








        loadOverlayTabContent: function (tabName, matchId, $overlay) {
            const $tabContent = $overlay.find(`.tab-content[data-tab="${tabName}"]`);
            $tabContent.html('<div class="loading"><img class="rotating-img" src="/wp-content/uploads/2025/03/spinner.png" ></div>');

            // Load content based on tab name
            switch (tabName) {
                case 'events':
                    this.loadMatchEventsOverlay(matchId, $tabContent);
                    break;
                case 'stats':
                    this.loadMatchStatsOverlay(matchId, $tabContent);
                    break;
                case 'lineup':
                    this.loadMatchLineupOverlay(matchId, $tabContent);
                    break;
                case 'standings':
                    this.loadMatchStandingsOverlay(matchId, $tabContent);
                    break;
            }

            // Determine if match is live using the match item's status.
            const $matchItem = $(`.match-item[data-match-id="${matchId}"]`);
            const status = $matchItem.data('match-status');

            // If the match is live, set an auto-refresh interval for this tab.
            if (this.LIVE_STATUSES.indexOf(status) !== -1) {
                // Clear an existing interval for this tab, if any.
                if (this.state.overlayTabIntervals[tabName]) {
                    clearInterval(this.state.overlayTabIntervals[tabName]);
                }
                let intervalTime;
                if (tabName === 'events') intervalTime = INTERVALS.events;
                else if (tabName === 'stats') intervalTime = INTERVALS.statistics;
                else if (tabName === 'lineup') intervalTime = INTERVALS.lineups;
                else if (tabName === 'standings') intervalTime = 300000; // For example, 5 minutes for standings

                // Set the auto-refresh interval for this tab.
                this.state.overlayTabIntervals[tabName] = setInterval(() => {
                    this.loadOverlayTabContent(tabName, matchId, $overlay);
                }, intervalTime);
            } else {
                // If match is not live, clear any existing auto-refresh for this tab.
                if (this.state.overlayTabIntervals[tabName]) {
                    clearInterval(this.state.overlayTabIntervals[tabName]);
                    delete this.state.overlayTabIntervals[tabName];
                }
            }
        },


        loadMatchEventsOverlay: function (matchId, $tabContent) {
            const self = this;
            const $matchItem = $(`.match-item[data-match-id="${matchId}"]`);
            const status = $matchItem.data('match-status');

            if (status === 'NS' || status === 'POSTPONED') {
                $tabContent.html('<div class="no-events-msg">No events available for this match.</div>');
                return;
            }

            const cachedEvents = this.eventsCache.get(matchId);
            if (cachedEvents) {
                self.renderEventsOverlayContent(matchId, cachedEvents, $tabContent);
                return;
            }

            $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_match_events',
                    nonce: livefotAjax.nonce,
                    match_id: matchId
                },
                dataType: 'json'
            }).done(function (response) {
                if (response.success !== false && response.data) {
                    self.eventsCache.set(matchId, response.data);
                    self.renderEventsOverlayContent(matchId, response.data, $tabContent);
                } else {
                    $tabContent.html('<div class="no-events-msg">No events available for this match.</div>');
                }
            }).fail(function () {
                $tabContent.html('<div class="error-msg">Failed to load events.</div>');
            });
        },

        renderEventsOverlayContent: function (matchId, eventsArray, $tabContent) {
            const showImportant = !!this.state.showImportantEventsByMatch[matchId];
            const toggleButtonLabel = showImportant ? 'Show All Events' : 'Show Important Only';
            const toggleButtonHtml = `
                <button class="toggle-important-events control-button" data-match-id="${matchId}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="svg-icon" style="width: 1.2em; height: 1.2em; vertical-align: middle; fill: currentColor; overflow: hidden;" viewBox="0 0 1024 1024" version="1.1"><path d="M504.7 138.1c-91.4 0-175.4 31.4-241.9 83.9l51.6 63.6c52.4-41.1 118.5-65.7 190.3-65.7 170.5 0 308.7 138.2 308.7 308.7 0 21.9-2.3 43.3-6.7 64l79.5 20.1c5.9-27.1 9.1-55.2 9.1-84 0-215.7-174.9-390.6-390.6-390.6zM702.4 765.8c-53.6 44.7-122.5 71.6-197.7 71.6-170.5 0-308.7-138.2-308.7-308.7 0-36.4 6.3-71.3 17.9-103.7l-79.7-20.1c-13 38.9-20.1 80.6-20.1 123.9 0 215.7 174.9 390.6 390.6 390.6 94.8 0 181.7-33.8 249.3-89.9l-51.6-63.7z" fill="#242424"/><path d="M173.5 416.4m-40.9 0a40.9 40.9 0 1 0 81.8 0 40.9 40.9 0 1 0-81.8 0Z" fill="#242424"/><path d="M286.5 256.4m-40.9 0a40.9 40.9 0 1 0 81.8 0 40.9 40.9 0 1 0-81.8 0Z" fill="#242424"/><path d="M727.5 799.4m-40.9 0a40.9 40.9 0 1 0 81.8 0 40.9 40.9 0 1 0-81.8 0Z" fill="#242424"/><path d="M845.8 605.8m-40.9 0a40.9 40.9 0 1 0 81.8 0 40.9 40.9 0 1 0-81.8 0Z" fill="#242424"/><path d="M33.5 528.4c-14.1-17.7-11.2-43.5 6.5-57.6l108-86.1c17.7-14.1 43.5-11.2 57.6 6.5 14.1 17.7 11.2 43.5-6.5 57.6L91 534.9c-17.7 14.1-43.4 11.2-57.5-6.5z" fill="#242424"/><path d="M285.2 556.4c-17.7 14.1-43.5 11.2-57.6-6.5l-86.1-108c-14.1-17.7-11.2-43.5 6.5-57.6 17.7-14.1 43.5-11.2 57.6 6.5l86.1 108c14.1 17.7 11.2 43.5-6.5 57.6zM977.7 483.6c15.4 16.5 14.5 42.5-2 57.9l-101 94.2c-16.5 15.4-42.5 14.5-57.9-2-15.4-16.5-14.5-42.5 2-57.9l101-94.2c16.6-15.4 42.5-14.5 57.9 2z" fill="#242424"/><path d="M723.2 474.1c16.5-15.4 42.5-14.5 57.9 2l94.2 101c15.4 16.5 14.5 42.5-2 57.9-16.5 15.4-42.5 14.5-57.9-2l-94.2-101c-15.5-16.6-14.6-42.5 2-57.9z" fill="#242424"/></svg>
                    ${toggleButtonLabel}
                </button>
            `;

            let filteredEvents = [];
            if (showImportant) {
                filteredEvents = this.filterImportantEvents(eventsArray);
            } else {
                filteredEvents = eventsArray.slice();
            }

            const contentHtml = `
                <div class="events-popup">
                    <div class="events-controls">
                        ${toggleButtonHtml}
                    </div>
                    <div class="events-list-container"></div>
                </div>
            `;
            $tabContent.html(contentHtml);

            const $matchItem = $(`.match-item[data-match-id="${matchId}"]`);
            const localTeamId = $matchItem.data('local-team-id');
            const visitorTeamId = $matchItem.data('visitor-team-id');

            const finalHtml = this.renderEvents(filteredEvents, localTeamId, visitorTeamId);
            $tabContent.find('.events-list-container').html(finalHtml);
        },

        filterImportantEvents: function (eventsArray) {
            const importantTypes = [
                'goal',
                'penalty',
                'own-goal',
                'pen_shootout_goal',
                'redcard',
                'yellowred'
            ];
            return eventsArray.filter(ev => importantTypes.includes(ev.type));
        },

        loadMatchStatsOverlay: function (matchId, $tabContent) {
            const self = this;
            const cachedStats = this.statsCache.get(matchId);
            if (cachedStats) {
                const statsHtml = self.renderStats(cachedStats);
                $tabContent.html(statsHtml);
                return;
            }

            $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_match_stats',
                    nonce: livefotAjax.nonce,
                    match_id: matchId
                },
                dataType: 'json'
            }).done(function (response) {
                if (response.success === true && response.data) {
                    self.statsCache.set(matchId, response.data);
                    const statsHtml = self.renderStats(response.data);
                    $tabContent.html(statsHtml);
                } else {
                    $tabContent.html('<div class="error-msg">Failed to load statistics.</div>');
                }
            }).fail(function () {
                $tabContent.html('<div class="error-msg">Failed to load statistics.</div>');
            });
        },

        // [LINEUP CODE START]
        loadMatchLineupOverlay: function (matchId, $tabContent) {
            const self = this;
            const cachedLineup = this.lineupCache.get(matchId);
            if (cachedLineup) {
                const lineupHtml = self.renderLineup(cachedLineup);
                $tabContent.html(lineupHtml);
                return;
            }

            $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_match_lineup',
                    nonce: livefotAjax.nonce,
                    match_id: matchId
                },
                dataType: 'json'
            }).done(function (response) {
                if (response.success !== false && response.data) {
                    self.lineupCache.set(matchId, response.data);
                    const lineupHtml = self.renderLineup(response.data);
                    $tabContent.html(lineupHtml);
                } else {
                    $tabContent.html('<div class="no-lineup-msg">No lineup available for this match.</div>');
                }
            }).fail(function () {
                $tabContent.html('<div class="error-msg">Failed to load lineup.</div>');
            });
        },

        /**
         * Render the lineup, pitch, bench, plus event icons, etc.
         * `data` should contain at least:
         *   data.localTeam = { teamData, players, ... }
         *   data.visitorTeam = { teamData, players, ... }
         *   data.matchEvents (optional) for event icons
         */
        renderLineup: function (data) {
            if (!data || !data.localTeam || !data.visitorTeam) {
                return `<div class="no-lineup-msg">No lineup available for this match.</div>`;
            }

            const teams = [
                {
                    teamData: data.localTeam.teamData,
                    players: data.localTeam.players,
                    isHome: true
                },
                {
                    teamData: data.visitorTeam.teamData,
                    players: data.visitorTeam.players,
                    isHome: false
                }
            ];
            const matchEvents = data.matchEvents || [];

            // Helper to retrieve events or related events for a given player
            function getPlayerEvents(playerId) {
                return matchEvents.filter(ev => ev.PlayerId === playerId);
            }
            function getRelatedEvents(playerId) {
                return matchEvents.filter(ev => ev.RelatedPlayerId === playerId);
            }

            // Show icons next to players (goals, cards, etc.)
            function renderEventIcons(events, relatedEvents) {
                let icons = '';
                events.forEach(event => {
                    switch (event.Type) {
                        case 'goal':
                            icons += `<img src="${iconBase}goal.svg" alt="Goal" style="width:16px;height:16px;vertical-align:middle;" title="Goal" />`;
                            if (event.isPenalty) {
                                icons += ' (P)';
                            }
                            break;
                        case 'penalty':
                            /* icons += ' ⚽(P)';*/
                            icons += `<img src="${iconBase}penalty.svg" alt="Goal Penalty" style="width:16px;height:16px;vertical-align:middle;" title="Goal Penalty" />`;
                            break;
                        case 'own-goal':
                            icons += `<img src="${iconBase}owngoal.svg" alt="Own Goal" style="width:16px;height:16px;vertical-align:middle;" title="Own Goal"/>`;
                            break;
                        case 'yellowcard':
                            icons += `<img src="${iconBase}yellowcard.svg" alt="Yellow Card" style="width:16px;height:16px;vertical-align:middle;" title="Yellow Card"/>`;
                            break;
                        case 'var':
                            icons += `<img src="${iconBase}var.svg" alt="Var" style="width:16px;height:16px;vertical-align:middle;" title="Var"/>`;
                            break;
                        case 'pen_shootout_miss':
                            icons += `<img src="${iconBase}missed penalty.svg" alt="Var" style="width:16px;height:16px;vertical-align:middle;" title="missed penalty"/>`;
                            break;
                        case 'missed_penalty':
                            icons += `<img src="${iconBase}missed penalty.svg" alt="Var" style="width:16px;height:16px;vertical-align:middle;" title="missed penalty normal"/>`;
                            break;
                        case 'redcard':
                            icons += `<img src="${iconBase}redcard.svg" alt="Red Card" style="width:16px;height:16px;vertical-align:middle;" title="Red Card"/>`;
                            break;
                        case 'yellowred':
                            icons += `<img src="${iconBase}yellowred.svg" alt="Second Yellow Card" style="width:16px;height:16px;vertical-align:middle;" title="Second Yellow Card"/>`;
                            break;
                        case 'substitution':
                            icons += `<img src="${iconBase}subin.svg" alt="Substitution In" style="width:16px;height:16px;vertical-align:middle;" title="Substitution"/>`;
                            break;
                        default:
                            break;
                    }
                });

                // For related events, e.g. assists or "substitution out"
                relatedEvents.forEach(event => {
                    if (event.Type === 'goal') {
                        icons += `<img src="${iconBase}assists.svg" alt="Assist" style="width:16px;height:16px;vertical-align:middle;" title="Assist"/>`;
                    } else if (event.Type === 'substitution' || event.Type === 'substitution-out') {
                        icons += `<img src="${iconBase}subout.svg" alt="Substitution Out" style="width:16px;height:16px;vertical-align:middle;" title="Sub Out"/>`;
                    }
                });
                return icons;
            }

            // --- Formation & pitch logic ---
            function generateCoordinatesForFormation(formationStr) {
                // parse something like "4-3-3" => [4,3,3]
                const parts = (formationStr || "4-3-3")
                    .split("-")
                    .map(x => parseInt(x, 10))
                    .filter(n => !isNaN(n) && n > 0);

                const coordMap = {};
                // We'll fix GK at position 1
                coordMap[1] = { top: "92%", left: "50%" };

                // The rest lines: start from top=82% to top=55% (for example)
                const lineCount = parts.length;
                const startTop = 82;
                const endTop = 55;
                const step = (lineCount > 1) ? (startTop - endTop) / (lineCount - 1) : 0;
                const lineTops = [];
                for (let i = 0; i < lineCount; i++) {
                    lineTops.push(`${startTop - i * step}%`);
                }

                let nextPos = 2;
                for (let i = 0; i < parts.length; i++) {
                    const countInLine = parts[i];
                    const topVal = lineTops[i];
                    const coordsForLine = distributeHorizontally(countInLine, topVal);
                    coordsForLine.forEach(posObj => {
                        if (nextPos <= 11) {
                            coordMap[nextPos] = posObj;
                            nextPos++;
                        }
                    });
                }
                // console.log(coordMap);
                return coordMap;
            }
            function distributeHorizontally(count, top) {
                if (count <= 0) return [];
                if (count === 1) {
                    return [{ top, left: "50%" }];
                }

                const coords = [];
                const leftStart = 100 / (count + 1);
                const step = 100 / (count + 1);

                for (let i = 1; i <= count; i++) {
                    let leftVal = leftStart * i;

                    // Ajustar los extremos (los laterales)
                    if (i === 1) {
                        leftVal -= 5;
                    } else if (i === count) {
                        leftVal += 5;
                    }

                    leftVal = Math.max(0, Math.min(100, leftVal));
                    coords.push({ top, left: `${leftVal}%` });
                }
                // console.log(coords);

                return coords;
            }
            function mirrorCoordinatesForTeamB(coordMapTeamA) {
                const coordMapTeamB = {};
                for (let pos in coordMapTeamA) {
                    const { top, left } = coordMapTeamA[pos];
                    // For position 1 (goalkeeper), set to 4% directly
                    if (pos === '1') {
                        coordMapTeamB[pos] = { top: "90px", left };
                    } else {
                        const newTopVal = 100 - parseFloat(top);
                        coordMapTeamB[pos] = { top: newTopVal + "%", left };
                    }
                    //                     const newTopVal = 100 - parseFloat(top);
                    //                     coordMapTeamB[pos] = { top: newTopVal + "%", left };
                }
                return coordMapTeamB;
            }

            function buildLineup(team) {
                const formationStr = team.teamData.TeamFormation || "4-3-3";
                const parts = formationStr
                    .split("-")
                    .map(x => parseInt(x, 10))
                    .filter(n => !isNaN(n) && n > 0);

                const coordMapA = generateCoordinatesForFormation(formationStr);
                const coordMap = team.isHome
                    ? coordMapA
                    : mirrorCoordinatesForTeamB(coordMapA);

                const slots = Array(12).fill(null);

                const lineupPlayers = team.players.filter(p => p.Type === 'lineup');
                lineupPlayers.forEach(p => {
                    const pos = parseInt(p.FormationPosition, 10);
                    if (pos >= 1 && pos <= 11) {
                        slots[pos] = p;
                    }
                });

                function formatPlayerName(playerName) {
                    if (!playerName) return '';
                    const parts = playerName.split(' ');
                    if (parts.length < 3) {
                        return `<span class="full-name">${playerName}</span>`;
                    }
                    const firstInitial = parts[0];
                    const secondPart = parts[1];
                    const rest = parts.slice(2).join(' ');
                    // 					return `${firstInitial} ${secondPart}<br>${rest}`;
                    return `<span class="name-1">${firstInitial} ${secondPart}</span><br><span class="name-2">${rest}</span>`;

                }

                const markers = Object.keys(coordMap).map(pos => {
                    const player = slots[pos];
                    if (!player) return '';
                    const { top, left } = coordMap[pos];
                    const playerEvents = getPlayerEvents(player.PlayerId);
                    const relatedEvents = getRelatedEvents(player.PlayerId);
                    const icons = renderEventIcons(playerEvents, relatedEvents);
                    const imgUrl = player.LogoPath || 'https://via.placeholder.com/50';
                    const cMark = player.Captain ? ' (C)' : '';
                    const formattedName = formatPlayerName(player.PlayerName);
                    return `
                        <div class="player-marker ${team.isHome ? 'team-a' : 'team-b'}"
                            style="top:${top}; left:${left};">
                            <img src="${imgUrl}" alt="${player.PlayerName}" class="player-image"/>
                            <div class="player-info">
                             <strong>${player.Number || ''}</strong>  <br> ${formattedName}${cMark} ${icons}
                            </div>
                        </div>
                    `;
                }).join('');

                const benchPlayers = team.players.filter(p => p.Type === 'bench');
                let benchHtml = '';
                if (benchPlayers.length > 0) {
                    benchHtml = `
                        <div class="bench-section" style="display:none;">
                            <ul class="bench-list">
                                ${benchPlayers.map(p => {
                        const playerEvents = getPlayerEvents(p.PlayerId);
                        const relEvents = getRelatedEvents(p.PlayerId);
                        const icons = renderEventIcons(playerEvents, relEvents);
                        const cMark = p.Captain ? ' (C)' : '';
                        return `
                                        <li class="bench-item">
                                            <img src="${p.LogoPath || 'https://via.placeholder.com/24'}" 
                                                alt="${p.PlayerName}" class="bench-player-image"/>
                                            <div class="bench-player-details">
                                                <span class="bench-player-name">
                                                    ${p.PlayerName} (#${p.Number || ''})${cMark}
                                                </span>
                                                <span class="bench-player-position">${p.Position || ''}</span>
                                                <span class="bench-player-icons">${icons}</span>
                                            </div>
                                        </li>
                                    `;
                    }).join('')}
                            </ul>
                        </div>
                    `;
                }

                const teamLogo = team.teamData.LogoPath || 'https://via.placeholder.com/50';
                const teamName = team.teamData.Name || 'Unknown Team';
                const formationLabel = team.teamData.TeamFormation || 'Unknown';

                return {
                    pitchMarkers: markers,
                    benchHtml,
                    headerHtml: `
                        <div class="team-info-block">
                            <div class="team-header" style="cursor:pointer;">
                                <div class="team-header-content">
                                    <div class="team-logo_name">
                                        <img src="${teamLogo}" alt="${teamName}" class="team-logo"/>
                                        <span class="team-name">
                                            ${teamName} - Bench (${benchPlayers.length})
                                        </span>
                                    </div>
                                    <div class="team-toggle">
                                        <svg class="toggle-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" 
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                            stroke-linejoin="round" style="transform: rotate(0deg); transition: transform 0.3s ease;">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            ${benchHtml}
                        </div>
                    `
                };
            }

            function renderPitch(markersA, markersB, teamA, teamB) {
                return `
                    <div class="pitch-container">
                        <div class="football-pitch">
                            <div class="field-markings">
                                <div class="center-circle"></div>
                                <div class="center-line"></div>
                                <div class="penalty-area-top"></div>
                                <div class="penalty-area-bottom"></div>
                                <div class="goal-area goal-area-top"></div>
                                <div class="goal-area goal-area-bottom"></div>
                                <div class="goal goal-top"></div>
                                <div class="goal goal-bottom"></div>
                                <div class="corner-arc corner-arc-top-left"></div>
                                <div class="corner-arc corner-arc-top-right"></div>
                                <div class="corner-arc corner-arc-bottom-left"></div>
                                <div class="corner-arc corner-arc-bottom-right"></div>
                                <div class="penalty-mark"></div>
                                <div class="penalty-mark penalty-mark-bottom"></div>
                            </div>
                            <div class="team-corner-info team-corner-info-top">
                                <div class="team-corner-name">
                                    ${teamB.teamData.Name || 'Away'} 
                                </div>
                                <div class="team-corner-formation">
                                    ${teamB.teamData.TeamFormation || 'Formation'}
                                </div>
                            </div>
                            <div class="team-corner-info team-corner-info-bottom">
                                <div class="team-corner-name">
                                    ${teamA.teamData.Name || 'Home'}
                                </div>
                                <div class="team-corner-formation">
                                    ${teamA.teamData.TeamFormation || 'Formation'}
                                </div>
                            </div>
                            ${markersA}
                            ${markersB}
                        </div>
                    </div>
                `;
            }

            const lineupA = buildLineup(teams[0]);
            const lineupB = buildLineup(teams[1]);

            return `
        <div class="lineup-content">
            ${renderPitch(lineupA.pitchMarkers, lineupB.pitchMarkers, teams[0], teams[1])}
            <div class="teams-info">
                ${lineupA.headerHtml}
                ${lineupB.headerHtml}
            </div>
        </div>
            `;
        },
        // [LINEUP CODE END]

        loadMatchStandingsOverlay: function (matchId, $tabContent) {
            const self = this;
            const $matchItem = $(`.match-item[data-match-id="${matchId}"]`);
            const league_id = $matchItem.data('league-id') || 0;
            const group_id = $matchItem.data('group-id') || 0;
            const season_id = $matchItem.data('season-id') || 0;
            if (!league_id || !season_id) {
                $tabContent.html('<div class="error-standings">Invalid parameters for standings.</div>');
                return;
            }
            let standingsKey = `league_${league_id}_season_${season_id}`;
            if (group_id !== 0) {
                standingsKey += `_group_${group_id}`;
            }

            const cached = self.standingsCache.get(standingsKey);
            if (cached) {
                const standingsHtml = self.buildStandingsHtml(cached);
                $tabContent.html(standingsHtml);
                return;
            }

            $.ajax({
                url: livefotAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_standings',
                    nonce: livefotAjax.nonce,
                    match_id: matchId,
                    league_id: league_id,
                    group_id: group_id,
                    season_id: season_id
                },
                dataType: 'json'
            }).done(function (response) {
                if (response.success && response.data && response.data.standings) {
                    self.standingsCache.set(standingsKey, response.data.standings);
                    self.state.openStandingsMatches[matchId] = response.data.standings;
                    const standingsHtml = self.buildStandingsHtml(response.data.standings);
                    $tabContent.html(standingsHtml);
                } else {
                    $tabContent.html('<div class="error-standings">No standings available for this league.</div>');
                }
            }).fail(function () {
                $tabContent.html('<div class="error-standings">Failed to load standings data.</div>');
            });
        },

        // ========== RENDER FUNCTIONS FOR TABS ==========

        renderEvents: function (events, localTeamId, visitorTeamId) {
            if (!events || events.length === 0) {
                return `
                    <div class="events-popup">
                        <h4 class="events-title">Match Events</h4>
                        <div class="no-events-msg">No events available for this match.</div>
                    </div>
                `;
            }

            const eventIcons = {
                goal: `<img src="${iconBase}goal.svg" alt="Goal"/>`,
                penalty: `<img src="${iconBase}penalty.svg" alt="Penalty"/>`,
                'own-goal': `<img src="${iconBase}owngoal.svg" alt="Own Goal"/>`,
                pen_shootout_goal: `<img src="${iconBase}penalty.svg" alt="Penalty Shootout Goal"/>`,
                yellowcard: `<img src="${iconBase}yellowcard.svg" alt="Yellow Card"/>`,
                var: `<img src="${iconBase}var.svg" alt="Var"/>`,
                pen_shootout_miss: `<img src="${iconBase}missed penalty.svg" alt="missed penalty"/>`,
                missed_penalty: `<img src="${iconBase}missed penalty.svg" alt="missed penalty match"/>`,
                redcard: `<img src="${iconBase}redcard.svg" alt="Red Card"/>`,
                yellowred: `<img src="${iconBase}yellowred.svg" alt="Yellow Red Card"/>`,
                substitution: `<img src="${iconBase}substitute.svg" alt="Substitution"/>`
            };

            function getEventMinute(event) {
                const baseMinute = parseInt(event.minute) || 0;
                const extraMinute = parseInt(event.extra_minute) || 0;
                return (baseMinute * 1000) + extraMinute;
            }

            function renderEventItem(event) {
                const icon = eventIcons[event.type] || event.type;
                const playerName = event.player_name || 'Unknown Player';
                const minute = event.minute + (event.extra_minute ? '+' + event.extra_minute : '');
                const reason = event.reason ? ' - ' + event.reason : '';

                const labelAssist = icon.includes('substitute.svg') ? 'Out:' : 'Assist by:';
                const relatedPlayer = event.related_player_name
                    ? `<br><span class="assist-by">${labelAssist} ${event.related_player_name}</span>`
                    : '';
                const result = event.result ? ` [${event.result}]` : '';
                const playerImg = (event.player && event.player.image_path)
                    ? event.player.image_path
                    : 'https://cdn.sportmonks.com/images/soccer/placeholder.png?text=?';
                const teamLogo = (event.team && event.team.logo_path)
                    ? event.team.logo_path
                    : 'https://cdn.sportmonks.com/images/soccer/placeholder.png?text=T';
                const teamSide = (event.team_id === localTeamId) ? 'left' : 'right';

                return `
					<li class="event-item ${teamSide}-event">
						<span class="event-time" title="${minute}'">${minute}'</span>
						<span class="event-icon">${icon}</span>
						<img src="${teamLogo}" class="event-team-logo" alt="Team Logo" />
						<img src="${playerImg}" class="event-player-image" alt="${playerName}" />
						<span class="event-player">${playerName}${relatedPlayer}</span>
						<span class="event-reason">${reason}${result}</span>
					</li>
				`;
            }

            const sortedEvents = [...events].sort((a, b) => getEventMinute(b) - getEventMinute(a));
            const eventsHtml = sortedEvents.map(event => renderEventItem(event)).join('');

            return `
                <div class="events-popup">
                    <h4 class="events-title">Match Events</h4>
                    <ul class="events-list">
                        ${eventsHtml}
                        ${!eventsHtml ? '<li class="no-events-msg">No events</li>' : ''}
                    </ul>
                </div>
            `;
        },

        renderStats: function (statsData) {
            if (!statsData || !statsData.localTeam || !statsData.visitorTeam) {
                return `<div class="no-stats-msg">No statistics available for this match.</div>`;
            }
            const { localTeam, visitorTeam } = statsData;
            const statMappings = {
                Possessiontime: "Possession",
                Goals: "Goals",
                Fouls: "Fouls",
                Corners: "Corners",
                Offsides: "Offsides",
                Yellowcards: "Yellow Cards",
                Redcards: "Red Cards",
                Saves: "Saves",
                Substitutions: "Substitutions",
                Penalties: "Penalties",
                Injuries: "Injuries",
                Tackles: "Tackles",
                Attacks: "Attacks",
                Dangerous_attacks: "Dangerous Attacks",
                Passes: {
                    Total: "Passes Total",
                    Accurate: "Passes Accurate",
                    Percentage: "Pass Accuracy"
                },
                Shots: {
                    Total: "Shots Total",
                    Ongoal: "Shots On Goal",
                    Blocked: "Shots Blocked",
                    Offgoal: "Shots Off Goal",
                    Insidebox: "Shots Inside Box",
                    Outsidebox: "Shots Outside Box"
                },
                Goal_kick: "Goal Kicks",
                Goal_attempts: "Goal Attempts",
                Free_kick: "Free Kicks",
                Throw_in: "Throw Ins",
                Ball_safe: "Ball Safe"
            };

            const desiredStatsOrder = [
                "Possession",
                "Goals",
                "Fouls",
                "Corners",
                "Offsides",
                "Yellow Cards",
                "Red Cards",
                "Substitutions",
                "Penalties",
                "Injuries",
                "Dangerous Attacks",
                "Passes Total",
                "Pass Accuracy",
                "Shots Total",
                "Shots On Goal",
                "Shots Blocked",
                "Shots Off Goal",
                "Shots Inside Box",
                "Shots Outside Box",
                "Goal Kicks",
                "Goal Attempts",
                "Free Kicks",
                "Throw Ins"
            ];

            function formatValue(value, suffix = '') {
                if (value !== null && value !== undefined && value !== "") {
                    return `${value}${suffix}`;
                }
                return 'N/A';
            }

            function extractStats(team) {
                const stats = team.stats;
                const extracted = {};
                for (const key in statMappings) {
                    if (statMappings.hasOwnProperty(key) && stats.hasOwnProperty(key)) {
                        if (typeof statMappings[key] === 'object') {
                            for (const subKey in statMappings[key]) {
                                if (statMappings[key].hasOwnProperty(subKey) && stats[key].hasOwnProperty(subKey)) {
                                    extracted[statMappings[key][subKey]] = stats[key][subKey];
                                }
                            }
                        } else {
                            extracted[statMappings[key]] = stats[key];
                        }
                    }
                }
                return extracted;
            }

            const localStats = extractStats(localTeam);
            const visitorStats = extractStats(visitorTeam);

            function calculateBarWidth(value1, value2) {
                const num1 = parseFloat(value1);
                const num2 = parseFloat(value2);
                if (isNaN(num1) || isNaN(num2)) {
                    return { left: 50, right: 50 };
                }
                const max = Math.max(num1, num2) || 1;
                return {
                    left: ((num1 / max) * 100).toFixed(2),
                    right: ((num2 / max) * 100).toFixed(2)
                };
            }

            const comparisonRows = desiredStatsOrder.map(label => {
                const localValueRaw = localStats[label];
                const visitorValueRaw = visitorStats[label];
                const isPercentage = label.toLowerCase().includes("possession") || label.toLowerCase().includes("accuracy");
                const localValue = formatValue(localValueRaw, isPercentage ? '%' : '');
                const visitorValue = formatValue(visitorValueRaw, isPercentage ? '%' : '');
                const barWidths = (!isNaN(parseFloat(localValueRaw)) && !isNaN(parseFloat(visitorValueRaw)))
                    ? calculateBarWidth(localValueRaw, visitorValueRaw)
                    : { left: 50, right: 50 };

                if (localValueRaw === undefined && visitorValueRaw === undefined) {
                    return '';
                }

                return `
                <div class="stat-row">
                    <div class="stat-left">
                        <span class="stat-value">${localValue}</span>
                    </div>
                    <div class="stat-label">
                        <span>${label}</span>
                        ${(!isNaN(parseFloat(localValueRaw)) && !isNaN(parseFloat(visitorValueRaw)))
                        ? `
                                <div class="stat-bar">
                                    <div class="stat-fill-left" style="width: ${barWidths.left}%"></div>
                                    <div class="stat-fill-right" style="width: ${barWidths.right}%"></div>
                                </div>
                              `
                        : ''
                    }
                    </div>
                    <div class="stat-right">
                        <span class="stat-value">${visitorValue}</span>
                    </div>
                </div>
                `;
            }).join('');

            return `
                <div class="statistics-content">
                    <h4>Match Statistics</h4>
                    <div class="stats-comparison">
                        ${comparisonRows}
                    </div>
                    
                </div>
            `;
        },

        buildStandingsHtml: function (standings) {
            if (!standings || standings.length === 0) {
                return `
                    <div class="standings-section">
                        <h4 class="standings-title">Standings</h4>
                        <div class="no-standings-msg">No standings available for this league.</div>
                    </div>
                `;
            }

            const descriptionMap = {};
            standings.forEach(function (team) {
                if (team.Description) {
                    if (!descriptionMap[team.Description] || team.Position < descriptionMap[team.Description]) {
                        descriptionMap[team.Description] = team.Position;
                    }
                }
            });

            const sortedDescriptions = Object.keys(descriptionMap).sort(function (a, b) {
                return descriptionMap[a] - descriptionMap[b];
            });

            //             const colorPalette = this.getColorPalette(sortedDescriptions.length);
            const colorPalette = [
                "#23cc8c",
                "#007BFF",
                "#FD7E14",
                "#6F42C1",
                "#FFC107",
                "#343A40",
                "#DC3545",
                "#20C997",
                "#6610F2",
                "#FF5733"
            ];
            const descriptionColorMap = {};
            sortedDescriptions.forEach(function (desc, index) {
                descriptionColorMap[desc] = colorPalette[index];
            });

            const hasStandingDescription = sortedDescriptions.length > 0;

            let html = `
                <div class="standings-section">
                    <h4 class="standings-title">Standings</h4>
                    <table class="standings-table">
                        <thead>
                            <tr>
                            ${hasStandingDescription ? '<th style="width:8px; padding: 0px !important;"></th>' : ''}
                                <th>Pos</th>
                                <th class="team-column">Team</th>
                                <th>Played</th>
                                <th>Wins</th>
                                <th>Draws</th>
                                <th>Lost</th>
                                <th>Goals</th>
                                <th>GD</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            standings.forEach(function (team) {

                let posStyle = '';
                if (team.Description && descriptionColorMap[team.Description]) {
                    posStyle = ` style="color:${descriptionColorMap[team.Description]}; font-weight: bold;"`;
                }

                html += `<tr>`;

                if (hasStandingDescription) {
                    let stCellStyle = team.Description && descriptionColorMap[team.Description]
                        ? ` style="background-color:${descriptionColorMap[team.Description]}; padding:0 !important;margin:0 !important; width:8px;"`
                        : ` style="padding:0 !important; margin:0 !important;"`;
                    html += `<td${stCellStyle}></td>`;
                }

                html += `<td>${team.Position}</td>`;
                html += `
                    <td class="team-column-data" style="display: flex !important; align-items: center !important; height: 50px !important; gap: 25px; font-weight: 800;">
    					<img src="${team.Team.LogoPath}" alt="${team.Team.Name}" style="margin-left: 10px !important;" class="standings-team-logo" />
    					<span class="truncate-text">${team.Team.Name}</span>

            	</td>
                    <td>${team.Played}</td>
                    <td>${team.Wins}</td>
                    <td>${team.Draws}</td>
                    <td>${team.Lost}</td>
                    <td>${team.Goals}</td>
                    <td>${team.GoalDiff}</td>
                    <td>${team.Points}</td>
                `;
                html += `</tr>`;
            });

            html += `
                        </tbody>
                    </table>
            `;

            if (hasStandingDescription) {
                html += `<div class="standings-descriptions">`;
                sortedDescriptions.forEach(function (desc) {
                    const color = descriptionColorMap[desc];
                    html += `
                        <div class="standing-description" style="display: flex; align-items: center; gap: 8px;">
                            <span class="description-color-box" style="background-color: ${color}; width: 12px; height: 12px; display: inline-block;"></span>
                            <span style="color: #000;">${desc}</span>
                        </div>
                    `;
                });
                html += `</div>`;
            }

            html += `</div>`;
            return html;
        },

        getColorPalette: function (n) {
            if (n === 1) {
                return ['hsl(120, 100%, 40%)'];
            }
            const colors = [];
            for (let i = 0; i < n; i++) {
                const hue = 120 - (120 * i) / (n - 1);
                colors.push(`hsl(${hue}, 100%, 40%)`);
            }
            return colors;
        },

        // ========== MISC RENDER HELPERS ==========
        updateDateDisplay: function () {
            $('.current-date').text(this.state.currentDate.toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));
        },

        renderGlobalControls: function () {
            return [
                '<div class="global-controls">',
                '<button class="control-button toggle-expand-all">',
                `<i class="icon-${this.state.allLeaguesExpanded ? 'collapse' : 'expand'}"></i>`,
                `<span>${this.state.allLeaguesExpanded ? 'Collapse All' : 'Expand All'}</span>`,
                '</button>',
                `<button class="control-button toggle-live${this.state.showLiveOnly ? ' active' : ''}">`,
                '<i class="icon-live"></i>',
                `<span>${this.state.showLiveOnly ? 'Show All Matches' : 'Show Live Only'}</span>`,
                '</button>',
                '</div>'
            ].join('');
        },

        renderLeagueSection: function (league, fixtures) {
            const fixturesWithLeague = fixtures.map(function (match) {
                match.league_id = league.league_info.id;
                match.group_id = match.group_id || 0;
                // Add league details from the league object
                match.league_name = league.league_info.name;
                match.league_country = league.league_info.country.name;
                // Only show stage if it isn’t “Regular Season”
                match.league_stage = (league.league_info.stage_name !== 'Regular Season') ? league.league_info.stage_name : '';
                return match;
            });
            const shouldShowStage = league.league_info.stage_name !== 'Regular Season';
            const stageHtml = shouldShowStage ? league.league_info.stage_name : '';
            const matchCount = fixturesWithLeague.length;

            return [
                `<div class="league-section${this.state.allLeaguesExpanded ? '' : ' collapsed'}">`,
                '<div class="league-header">',
                '<div class="league-header-content">',
                `<img src="${league.league_info.logo_path}" alt="${league.league_info.name}" class="league-logo">`,
                '<div class="league-info">',
                `<h3>${league.league_info.name}</h3>`,
                `<span class="league-country">${league.league_info.country.name}${shouldShowStage ? ' - ' + stageHtml : ''}</span>`,
                '</div>',
                `<span class="match-count">${matchCount}</span>`,
                '</div>',
                '<div class="league-toggle">',
                '<svg class="toggle-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">',
                '<polyline points="6 9 12 15 18 9"></polyline>',
                '</svg>',
                '</div>',
                '</div>',
                '<div class="matches-list">',
                fixturesWithLeague.map(this.renderMatch.bind(this)).join(''),
                '</div>',
                '</div>'
            ].join('');
        }
        ,




        renderMatch: function (match) {
            const status = this.getMatchStatus(match.time, match.scores);
            const scores = match.scores || {};
            const showScore = status.showScore && match.time.status !== 'NS';
            const isLocalWinner = match.aggregate?.winner === match.localTeam.id;
            const isVisitorWinner = match.aggregate?.winner === match.visitorTeam.id;
            const starIcon = '<span class="qualified-icon" title="Qualified">★</span>';
            const localTeamName = `${match.localTeam.name}${isLocalWinner ? ` ${starIcon}` : ''}`;
            const visitorTeamName = `${match.visitorTeam.name}${isVisitorWinner ? ` ${starIcon}` : ''}`;
            let aggregateHtml = '';
            if (match.aggregate) {
                if (match.aggregate.result) {
                    const parts = match.aggregate.result.split('-').map(s => parseInt(s.trim(), 10));
                    let aggregateScore;
                    if (parts.some(val => isNaN(val))) {
                        aggregateScore = "(-)";
                    } else {
                        aggregateScore = (match.localTeam.id === match.aggregate.localteam_id)
                            ? `${parts[0]} - ${parts[1]}`
                            : `${parts[1]} - ${parts[0]}`;
                    }
                    aggregateHtml = `<div class="aggregate-score">${aggregateScore}</div>`;
                } else {
                    aggregateHtml = `<div class="aggregate-score">(-)</div>`;
                }
            }

            const redCards = {};
            const localTeamId = String(match.localTeam.id);
            const visitorTeamId = String(match.visitorTeam.id);
            if (Array.isArray(match.red_cards)) {
                match.red_cards.forEach(card => {
                    const cardTeamId = String(card.team_id);
                    const cardCount = Number(card.count) || 0;
                    if (!redCards[cardTeamId]) {
                        redCards[cardTeamId] = 0;
                    }
                    redCards[cardTeamId] += cardCount;
                });
            }
            const redCardsLocal = redCards[localTeamId] || 0;
            const redCardsVisitor = redCards[visitorTeamId] || 0;

            const localTeamLogoHtml = `
                <div class="team-logo-container">
                    <img src="${match.localTeam.logo_path}" alt="${match.localTeam.name}" class="team-logo">
                </div>`;
            const visitorTeamLogoHtml = `
                <div class="team-logo-container">
                    <img src="${match.visitorTeam.logo_path}" alt="${match.visitorTeam.name}" class="team-logo">
                </div>`;

            const redCardLocalHtml = (redCardsLocal > 0)
                ? `<span class="red-card-icon" title="${redCardsLocal} Red Card${redCardsLocal > 1 ? 's' : ''}">
                     <img src="${iconBase}redcard.svg" alt="Red Card" style="width:16px;height:16px;vertical-align:middle;" />
                     ${redCardsLocal > 1 ? `<span class="red-card-count">${redCardsLocal}</span>` : ''}
                   </span>`
                : '';
            const redCardVisitorHtml = (redCardsVisitor > 0)
                ? `<span class="red-card-icon" title="${redCardsVisitor} Red Card${redCardsVisitor > 1 ? 's' : ''}">
                     <img src="${iconBase}redcard.svg" alt="Red Card" style="width:16px;height:16px;vertical-align:middle;" />
                     ${redCardsVisitor > 1 ? `<span class="red-card-count">${redCardsVisitor}</span>` : ''}
                   </span>`
                : '';

            return [
                `<div class="match-item"
    data-match-id="${match.id}"
    data-local-team-id="${match.localTeam.id}"
    data-visitor-team-id="${match.visitorTeam.id}"
    data-match-status="${match.time.status}"
    data-league-id="${match.league_id || 0}"
    data-group-id="${match.group_id || 0}"
    data-season-id="${match.season_id || 0}"
    data-league-name="${match.league_name}"
    data-league-country="${match.league_country}"
    data-league-stage="${match.league_stage || ''}">
`,
                '<div class="match-data-frame">',
                '<div class="match-results-frame">',
                `<div class="match-time">${status.html}</div>`,
                '<div class="match-teams">',

                '<div class="team home">',
                `<span class="team-name${isLocalWinner ? ' winner' : ''}">${localTeamName}</span>`,
                `<span class="card-team-name${isLocalWinner ? ' winner' : ''}">${redCardLocalHtml}</span>`,
                '</div>',

                '<div class="score-container">',
                aggregateHtml,
                '<div class="match-score">',
                localTeamLogoHtml,
                '<div class="match-score-results">',
                showScore ? `${scores.localteam_score} - ${scores.visitorteam_score}` : '',
                '</div>',
                visitorTeamLogoHtml,
                '</div>',
                '</div>',

                '<div class="team away">',
                `${redCardVisitorHtml}`,
                `<span class="team-name${isVisitorWinner ? ' winner' : ''}">${visitorTeamName}</span>`,
                '</div>',

                '</div>',
                //                 this.renderMatchActions(match.time.status, match.time),
                '</div>',
                '</div>',
                '</div>'
            ].join('');
        },

        renderMatchActions: function (status, time) {
            return `
                <div class="match-actions">
                    <button class="action-button match-details" title="Match Details">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                    </button>
                </div>
            `;
        },



        getMatchStatus: function (time, scores) {
            const result = {
                html: '',
                showScore: false
            };

            // Base minute (e.g. "45'")
            const baseMinute = (time.minute === null) ? '' : time.minute + "'";
            // Injury time is displayed inline (if available)
            const injuryTime = (time.status === MATCH_STATUSES.LIVE && time.injury_time !== null && time.injury_time > 0)
                ? `<span class="injury-time">+${time.injury_time}</span>` : '';
            // Added time is rendered on a new line under the minute
            const addedTime = (time.added_time !== null && time.added_time > 0)
                ? `<br><span class="added-time">+${time.added_time}</span>` : '';

            // TO TEST
            // const injuryTime = `<span class="injury-time">+3</span>`;
            // const addedTime = `<br><span class="added-time">+5</span>`;

            switch (time.status) {
                case MATCH_STATUSES.LIVE:
                    result.html = `<span class="status live">
                             <i class="icon-radio"></i>
                             ${baseMinute} ${injuryTime}${addedTime}
                           </span>`;
                    result.showScore = true;
                    break;
                case MATCH_STATUSES.ET:
                    result.html = `<span class="status et">
                             <i class="icon-extra-time"></i>
                             ${baseMinute} ${injuryTime}
                           </span>`;
                    result.showScore = true;
                    break;
                case MATCH_STATUSES.HT:
                    result.html = `<span class="status ht"><i class="icon-clock"></i>HT</span>`;
                    result.showScore = true;
                    break;
                case MATCH_STATUSES.PEN_LIVE:
                case MATCH_STATUSES.PEN:
                case MATCH_STATUSES.FT_PEN:
                    const localPenScore = scores?.localteam_pen_score || 'N/A';
                    const visitorPenScore = scores?.visitorteam_pen_score || 'N/A';
                    result.html = `<span class="status pen">
                             <i class="icon-target"></i>${time.status.replace('_', ' ')}<br>
                             ${localPenScore} - ${visitorPenScore}
                           </span>`;
                    result.showScore = true;
                    break;
                case MATCH_STATUSES.BREAK:
                case MATCH_STATUSES.INT:
                    result.html = `<span class="status break"><i class="icon-coffee"></i>Break</span>`;
                    result.showScore = true;
                    break;
                case MATCH_STATUSES.FT:
                    result.html = `<span class="status ft"><i class="icon-check"></i>FT</span>`;
                    result.showScore = true;
                    break;
                case MATCH_STATUSES.AET:
                    result.html = `<span class="status aet"><i class="icon-check"></i>AET</span>`;
                    result.showScore = true;
                    break;
                case MATCH_STATUSES.NS:
                    const matchTimeUTC = new Date(time.starting_at.date_time);
                    const utcOffsetMinutes = new Date().getTimezoneOffset();
                    const localOffset = -(utcOffsetMinutes / 60);
                    const matchTimeLocal = new Date(matchTimeUTC.getTime() + (localOffset * 60 * 60 * 1000));
                    const localTimeFormatted = matchTimeLocal.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                    result.html = `<span class="status upcoming">
                             <i class="icon-clock"></i>${localTimeFormatted}
                           </span>`;
                    break;
                case MATCH_STATUSES.POSTPONED:
                    result.html = `<span class="status postponed">
                             <i class="icon-alert-circle"></i>Postponed
                           </span>`;
                    break;
                default:
                    result.html = `<span class="status">
                             <i class="icon-help-circle"></i>${time.status}
                           </span>`;
            }
            return result;
        }
    };


    // ========== ON DOCUMENT READY ==========
    $(document).ready(function () {
        const manager = new MatchesManager();
        manager.init();

    });

})(jQuery);
