import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['month', 'body', 'prevButton', 'nextButton'];
    static values = {
        currentMonth: Number,
        currentYear: Number
    };

    connect() {
        const now = new Date();
        this.currentMonthValue = now.getMonth() + 1;
        this.currentYearValue = now.getFullYear();
        this.updateCalendar();
    }

    prevMonth() {
        if (this.currentMonthValue === 1) {
            this.currentMonthValue = 12;
            this.currentYearValue--;
        } else {
            this.currentMonthValue--;
        }
        this.updateCalendar();
    }

    nextMonth() {
        if (this.currentMonthValue === 12) {
            this.currentMonthValue = 1;
            this.currentYearValue++;
        } else {
            this.currentMonthValue++;
        }
        this.updateCalendar();
    }

    showAttendanceDetails(event) {
        const dayElement = event.currentTarget;
        const date = dayElement.dataset.date;
        const dayData = this.calendarData[date];

        if (!dayData || dayData.count === 0) return;

        // Create modal content
        const modalContent = document.createElement('div');
        modalContent.className = 'attendance-modal';
        modalContent.innerHTML = `
            <div class="attendance-modal-content">
                <div class="attendance-modal-header">
                    <h3>Attendance Details - ${new Date(date).toLocaleDateString()}</h3>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="attendance-modal-body">
                    <div class="attendance-groups">
                        <div class="attendance-group">
                            <h4>Morning (9-12)</h4>
                            <ul>
                                ${this.getChildrenList(dayData.children, '9-12')}
                            </ul>
                        </div>
                        <div class="attendance-group">
                            <h4>Afternoon (13-16)</h4>
                            <ul>
                                ${this.getChildrenList(dayData.children, '13-16')}
                            </ul>
                        </div>
                        <div class="attendance-group">
                            <h4>Full Day (9-16)</h4>
                            <ul>
                                ${this.getChildrenList(dayData.children, '9-16')}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to the page
        document.body.appendChild(modalContent);

        // Add event listener to close button
        const closeButton = modalContent.querySelector('.close-modal');
        closeButton.addEventListener('click', () => {
            modalContent.remove();
        });

        // Close modal when clicking outside
        modalContent.addEventListener('click', (e) => {
            if (e.target === modalContent) {
                modalContent.remove();
            }
        });
    }

    getChildrenList(children, halfDay) {
        const filteredChildren = children.filter(child => child.half_day === halfDay);
        if (filteredChildren.length === 0) {
            return '<li class="no-attendance">No children</li>';
        }
        return filteredChildren
            .map(child => `<li>${child.name}${child.id ? ` (ID: ${child.id})` : ''}</li>`)
            .join('');
    }

    async updateCalendar() {
        const response = await fetch(`/educator/calendar?month=${this.currentMonthValue}&year=${this.currentYearValue}`);
        const data = await response.json();
        
        // Store calendar data for later use
        this.calendarData = data.calendarData;
        
        // Update month display
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                          'July', 'August', 'September', 'October', 'November', 'December'];
        this.monthTarget.textContent = `${monthNames[this.currentMonthValue - 1]} ${this.currentYearValue}`;

        // Clear current calendar
        this.bodyTarget.innerHTML = '';

        // Get first day of month and total days
        const firstDay = new Date(this.currentYearValue, this.currentMonthValue - 1, 1);
        const lastDay = new Date(this.currentYearValue, this.currentMonthValue, 0);
        const totalDays = lastDay.getDate();
        const startingDay = firstDay.getDay();

        // Add empty cells for days before the first of the month
        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            this.bodyTarget.appendChild(emptyDay);
        }

        // Add days of the month
        for (let day = 1; day <= totalDays; day++) {
            const date = `${this.currentYearValue}-${String(this.currentMonthValue).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayData = data.calendarData[date] || { count: 0, children: [] };
            
            const dayElement = document.createElement('div');
            dayElement.className = `calendar-day ${dayData.count > 0 ? 'has-attendance' : ''}`;
            dayElement.dataset.date = date;
            
            if (dayData.count > 0) {
                dayElement.style.cursor = 'pointer';
                dayElement.addEventListener('click', this.showAttendanceDetails.bind(this));
            }
            
            let childrenList = '';
            if (dayData.count > 0) {
                // Group children by name
                const childrenByName = {};
                dayData.children.forEach(child => {
                    if (!childrenByName[child.name]) {
                        childrenByName[child.name] = [];
                    }
                    childrenByName[child.name].push(child.id);
                });

                // Create list of children with their IDs if there are duplicates
                childrenList = Object.entries(childrenByName)
                    .map(([name, ids]) => {
                        if (ids.length > 1) {
                            return `${name} (${ids.join(', ')})`;
                        }
                        return name;
                    })
                    .join(', ');
            }
            
            dayElement.innerHTML = `
                <span class="day-number">${day}</span>
                ${dayData.count > 0 ? `
                    <div class="attendance-info">
                        <span class="attendance-count">${dayData.count}</span>
                        <span class="attendance-label">Present</span>
                        <div class="children-list">${childrenList}</div>
                    </div>
                ` : ''}
            `;
            
            this.bodyTarget.appendChild(dayElement);
        }
    }
} 