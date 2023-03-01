/**
* Tock by Mr Chimp - github.com/mrchimp/tock
* Based on code by James Edwards:
*    sitepoint.com/creating-accurate-timers-in-javascript/
*/
// Implements Date.now() for ie lt 9
Date.now = Date.now || function() { return +new Date(); };
(function (root, factory)
{
        if (typeof define === 'function' && define.amd)
        {
                define(factory);
        }
        else if (typeof exports === 'object')
        {
                module.exports = factory();
        }
        else
        {
                root.Tock = factory();
        }
}(this, function ()
{
        var Tock = (function(options)
        {
                Tock.instances = (Tock.instances || 0) + 1;
                var go = false,
                timeout = null,
                missed_ticks = null,
                interval = options.interval || 10,
                countdown = options.countdown || false,
                start_time = 0,
                pause_time = 0,
                final_time = 0,
                duration_ms = 0,
                time = 0,
                elapsed = 0,
                format_daysleft = '%%D%%' + phrase['_d_shortform'] + ', %%H%%' + phrase['_h_shortform'] + ', %%M%%' + phrase['_m_shortform'] + ', %%S%%' + phrase['_s_shortform'],
                format_hoursleft = '%%H%%' + phrase['_h_shortform'] + ', %%M%%' + phrase['_m_shortform'] + ', %%S%%' + phrase['_s_shortform'],
                format_minutesleft = '%%M%%' + phrase['_m_shortform'] + ', %%S%%' + phrase['_s_shortform'],
                format_secondsleft = '%%S%%' + phrase['_s_shortform'],
                callback = options.callback || function(){},
                complete = options.complete || function(){};
                /**
                * Reset the clock
                */
                function reset()
                {
                        if (countdown)
                        {
                                return false;
                        }
                        stop();
                        start_time = 0;
                        time = 0;
                        elapsed = '0.0';
                }
                /**
                * Start the clock.
                */
                function start(time)
                {
                        start_time = time;
                        if (countdown)
                        {
                                _startCountdown(time);
                        }
                        else
                        {
                                _startTimer(0);
                        }
                }
                /**
                * Called every tick for countdown clocks.
                * i.e. once every this.interval ms
                */
                function _tick()
                {
                        time += interval;
                        elapsed = Math.floor(time / interval) / 10;
                        if (Math.round(elapsed) === elapsed)
                        {
                                elapsed += '.0';
                        }
                        var t = this,
                        diff = (Date.now() - start_time) - time,
                        next_interval_in = interval - diff;
                        if (callback !== undefined)
                        {
                                callback(this);
                        }
                        if (countdown && (duration_ms - time < 0))
                        {
                                final_time = 0;
                                go = false;
                                callback();
                                window.clearTimeout(this.timeout);
                                complete();
                                return;
                        }
                        if (next_interval_in <= 0)
                        {
                                this.missed_ticks = Math.floor(Math.abs(next_interval_in) / interval);
                                time += this.missed_ticks * interval;
                                if (go)
                                {
                                        _tick();
                                }
                        }
                        else
                        {
                                if (go)
                                {
                                        this.timeout = window.setTimeout(_tick, next_interval_in);
                                }
                        }
                }
                /**
                * Stop the clock.
                */
                function stop()
                {
                        go = false;
                        window.clearTimeout(this.timeout);
                        if (countdown)
                        {
                                final_time = duration_ms - time;
                        }
                        else
                        {
                                final_time = (Date.now() - start_time);
                        }
                }
                /**
                * Stop/start the clock.
                */
                function pause()
                {
                        if (go)
                        {
                                pause_time = lap();
                                stop();
                        }
                        else
                        {
                                if (pause_time)
                                {
                                        if (countdown)
                                        {
                                                _startCountdown(pause_time);
                                        }
                                        else
                                        {
                                                _startTimer(pause_time);
                                        }
                                }
                        }
                }
                /**
                * Get the current clock time in ms.
                * Use with Tock.msToTime() to make it look nice.
                */
                function lap()
                {
                        if (go)
                        {
                                var now;
                                if (countdown)
                                {
                                        now = duration_ms - (Date.now() - start_time);
                                }
                                else
                                {
                                        now = (Date.now() - start_time);
                                }
                                return now;
                        }
                        return pause_time || final_time;
                }
                /**
                * Format milliseconds as a string.
                */
                function msToTime(ms)
                {
                        if (ms <= 0)
                        {
                                return phrase['_ended'];
                        }
                        var seconds = Math.floor((ms / 1000) % 60).toString(),
                        minutes = Math.floor((ms / (1000 * 60)) % 60).toString(),
                        hours = Math.floor((ms / (1000 * 60 * 60)) % 24).toString(),
                        days = Math.floor((ms / (1000 * 60 * 60 * 24))).toString();
                        if (jQuery('#timelefttext').length > 0 && jQuery('#timelefttext').hasClass('js-lot-timer'))
                        {
                                jQuery('#timelefttext').removeClass('js-lot-timer');
                        }
                        if (days > 0)
                        {
                                if (jQuery('#timelefttext').length > 0 && jQuery('#timelefttext').hasClass('js-lot-timer') == false)
                                {
                                        jQuery('#timelefttext').addClass('js-lot-timer');
                                }
                                string = format_daysleft.replace(/%%D%%/g, days);
                                string = string.replace(/%%H%%/g, hours);
                                string = string.replace(/%%M%%/g, minutes);
                                string = string.replace(/%%S%%/g, seconds);
                        }
                        else if (days == 0 && hours > 0)
                        {
                                string = format_hoursleft.replace(/%%H%%/g, hours);
                                string = string.replace(/%%M%%/g, minutes);
                                string = string.replace(/%%S%%/g, seconds);
                        }
                        else if (days == 0 && hours == 0 && minutes > 0)
                        {
                                string = format_minutesleft.replace(/%%M%%/g, minutes);
                                string = string.replace(/%%S%%/g, seconds);
                                string = '<span class="red">' + string + '</span>';
                        }
                        else if (days == 0 && hours == 0 && minutes == 0 && seconds >= 0)
                        {
                                string = format_secondsleft.replace(/%%S%%/g, seconds);
                                string = '<span class="red">' + string + '</span>';
                        }
                        else
                        {
                                string = '<span class="red">' + phrase['_ended'] + '</span>';
                        }
                        return string;
                }
                /**
                * Convert a time string to milliseconds
                *
                * Possible inputs:
                * Sat Nov 29 2014 22:40:06 GMT-0500 (EST)
                */
                function timeToMS(datetime)
                {
                        var now = new Date().getTime();
                        var then = new Date(datetime).getTime();
                        var ms = then - now;
                        return ms;
                }
                /**
                * Called by Tock internally - use start() instead
                */
                function _startCountdown(duration)
                {
                        duration_ms = duration;
                        start_time = Date.now();
                        end_time = start_time + duration_ms;
                        time = 0;
                        elapsed = '0.0';
                        go = true;
                        _tick();
                        this.timeout = window.setTimeout(_tick, interval);
                }
                /**
                * Called by Tock internally - use start() instead
                */
                function _startTimer(start_offset)
                {
                        start_time = Date.now() - start_offset;
                        time = 0;
                        elapsed = '0.0';
                        go = true;
                        _tick();
                        this.timeout = window.setTimeout(_tick, interval);
                }
                return {
                        'start': start,
                        'pause': pause,
                        'stop': stop,
                        'reset': reset,
                        'lap': lap,
                        'msToTime': msToTime,
                        'timeToMS': timeToMS
                };
        });
        return Tock;
}));
