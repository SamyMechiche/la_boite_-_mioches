import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['month', 'body'];
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
        modalContent.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
        modalContent.innerHTML = `
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Attendance Details - ${new Date(date).toLocaleDateString()}</h3>
                    <button class="text-gray-400 hover:text-gray-500 focus:outline-none">&times;</button>
                </div>
                <div class="space-y-4">
                    <div class="attendance-group">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Morning (9-12)</h4>
                        <ul class="space-y-1">
                            ${this.getChildrenList(dayData.children, '9-12')}
                        </ul>
                    </div>
                    <div class="attendance-group">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Afternoon (13-16)</h4>
                        <ul class="space-y-1">
                            ${this.getChildrenList(dayData.children, '13-16')}
                        </ul>
                    </div>
                    <div class="attendance-group">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Full Day (9-16)</h4>
                        <ul class="space-y-1">
                            ${this.getChildrenList(dayData.children, '9-16')}
                        </ul>
                    </div>
                </div>
            </div>
        `;

        // Add modal to the page
        document.body.appendChild(modalContent);

        // Add event listener to close button
        const closeButton = modalContent.querySelector('button');
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
            return '<li class="text-gray-500 text-sm">No children</li>';
        }
        return filteredChildren
            .map(child => `<li class="text-sm text-gray-700">${child.name}${child.id ? ` (ID: ${child.id})` : ''}</li>`)
            .join('');
    }

    async updateCalendar() {
        try {
            const response = await fetch(`/educator/calendar?month=${this.currentMonthValue}&year=${this.currentYearValue}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
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
                emptyDay.className = 'bg-white p-2 min-h-[80px]';
                this.bodyTarget.appendChild(emptyDay);
            }

            // Add days of the month
            for (let day = 1; day <= totalDays; day++) {
                const date = `${this.currentYearValue}-${String(this.currentMonthValue).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayData = data.calendarData[date] || { count: 0, children: [] };
                
                const dayElement = document.createElement('div');
                dayElement.className = `bg-white p-2 min-h-[80px] ${dayData.count > 0 ? 'bg-primary-50 hover:bg-primary-100 cursor-pointer' : ''}`;
                dayElement.dataset.date = date;
                
                if (dayData.count > 0) {
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
                    <div class="flex flex-col h-full">
                        <span class="text-sm font-medium text-gray-900">${day}</span>
                        ${dayData.count > 0 ? `
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800">
                                    ${dayData.count} Present
                                </span>
                                <div class="mt-1 text-xs text-gray-500 truncate">${childrenList}</div>
                            </div>
                        ` : ''}
                    </div>
                `;
                
                this.bodyTarget.appendChild(dayElement);
            }

            // Add empty cells for remaining days in the last week
            const remainingDays = 7 - ((startingDay + totalDays) % 7);
            if (remainingDays < 7) {
                for (let i = 0; i < remainingDays; i++) {
                    const emptyDay = document.createElement('div');
                    emptyDay.className = 'bg-white p-2 min-h-[80px]';
                    this.bodyTarget.appendChild(emptyDay);
                }
            }
        } catch (error) {
            console.error('Error updating calendar:', error);
        }
    }
} 