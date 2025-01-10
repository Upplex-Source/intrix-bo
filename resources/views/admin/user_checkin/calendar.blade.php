<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.calendar' ) }}</h5>
                <div id="calendar" class="mb-3">
                    <!-- Calendar will render here -->
                </div>
                <section id="checkin-details" class="hidden">
                    <h6>{{ __( 'checkin_reward.checkin_details' ) }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __( 'checkin_reward.username' ) }}</th>
                                    <th>{{ __( 'checkin_reward.phone_number' ) }}</th>
                                    <th>{{ __( 'checkin_reward.total_checkin' ) }}</th>
                                </tr>
                            </thead>
                            <tbody id="checkin-details-body">
                                <!-- Static content will be injected here -->
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/main.min.css" rel="stylesheet">
<script src="
https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js
"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        // Initialize FullCalendar
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth', // Month view
            selectable: true, // Allows date selection
            events: events,
            dateClick: function (info) {
                const dateCheckins = sampleCheckins[info.dateStr] || [];
                displayCheckinDetails(dateCheckins);
            },
            datesSet: function (info) {

            },
        });

        // Static check-in data for selected dates
        const sampleCheckins = {
            '2025-01-08': [
                { username: 'John Doe', phone_number: '10:00 AM', total_checkin: '3' },
                { username: 'Jane Smith', phone_number: '11:30 AM', total_checkin: '10' },
            ],
            '2025-01-09': [
                { username: 'Alice Johnson', phone_number: '09:15 AM', total_checkin: '11' },
            ],
        };

        // Convert sample check-ins to events
        const events = Object.keys(sampleCheckins).map(date => ({
            title: `${sampleCheckins[date].length} Check-ins`,
            start: date,
            extendedProps: { checkins: sampleCheckins[date] }, // Add custom data
        }));

        // Render the calendar
        calendar.render();

        // Function to display check-in details
        function displayCheckinDetails(checkins) {
            const checkinDetailsSection = document.getElementById('checkin-details');
            const checkinDetailsBody = document.getElementById('checkin-details-body');

            // Clear previous content
            checkinDetailsBody.innerHTML = '';

            if (checkins && checkins.length > 0) {
                checkins.forEach(checkin => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${checkin.username}</td>
                        <td>${checkin.phone_number}</td>
                        <td>${checkin.total_checkin}</td>
                    `;
                    checkinDetailsBody.appendChild(row);
                });
                checkinDetailsSection.classList.remove('hidden');
            } else {
                checkinDetailsBody.innerHTML = `<tr><td colspan="3">No data available</td></tr>`;
                checkinDetailsSection.classList.remove('hidden');
            }
        }
    });
</script>

