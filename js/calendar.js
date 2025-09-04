document.addEventListener("DOMContentLoaded", function () {
  var calendar;
  var instanceCount = 0;
  var selectedDates = {};
  var selectedTimeSlots = {};
  var activeInstanceId = null;
  var currentPriceType = document.getElementById("amenity_price_type")
    ? document.getElementById("amenity_price_type").value
    : "price_per_hour";

  var prep_time_minutes =
    typeof Listing_Calendar !== "undefined" && Listing_Calendar.prep_time
      ? parseInt(Listing_Calendar.prep_time)
      : 0;

  // Function to create prep time event
  function createPrepTimeEvent(originalEvent) {
    if (prep_time_minutes <= 0) return null;

    const prepStart = moment(originalEvent.end);
    const prepEnd = prepStart.clone().add(prep_time_minutes, "minutes");

    return {
      title: "Prep Time",
      start: prepStart.utc().format(),
      end: prepEnd.utc().format(),
      editable: false,
      color: "#d4edda",
      textColor: "#155724",
      className: "fc-event-prep",
      originalEventId: originalEvent.id,
    };
  }

  setupStepObserver();
  const calendarStep = document.querySelector(
    '.form-step[data-step="calendar"]'
  );
  if (
    calendarStep &&
    calendarStep.classList.contains("active") &&
    calendarStep.style.display === "block"
  ) {
    setTimeout(function () {
      initializeCalendar();
    }, 200);
  }

  function setupStepObserver() {
    const targetNode = document.querySelector(
      '.form-step[data-step="calendar"]'
    );

    if (!targetNode) return;

    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.attributeName === "class") {
          const isActive = targetNode.classList.contains("active");
          const isVisible = targetNode.style.display === "block";

          if (isActive && isVisible) {
            //console.log("Calendar step became active - reinitializing...");
            setTimeout(function () {
              initializeCalendar();
            }, 200);
          }
        }
      });
    });

    // Configure and start the observer
    observer.observe(targetNode, {
      attributes: true,
      attributeFilter: ["class", "style"],
    });
  }

  // Check price type on change
  if (document.getElementById("amenity_price_type")) {
    document
      .getElementById("amenity_price_type")
      .addEventListener("change", function () {
        currentPriceType = this.value;
        //console.log("Current Price Type");
        //console.log(currentPriceType);
        updateAllInstancesUI();
        initializeCalendar();
        // Force update of bookings visibility
        document.querySelectorAll(".instance").forEach((instance) => {
          const instanceId = instance.getAttribute("data-instance");
          const showBookings = currentPriceType !== "price_per_hour";
          const eventsSection = instance.querySelector(
            `#events-for-instance-${instanceId}`
          );
          if (eventsSection) {
            eventsSection.style.display = showBookings ? "block" : "none";
            const heading = eventsSection.previousElementSibling;
            if (
              heading &&
              heading.tagName === "H3" &&
              heading.textContent.includes("My Bookings")
            ) {
              heading.style.display = showBookings ? "block" : "none";
            }
          }
        });
      });
  }

  // Check if Listing_Calendar is defined
  var booked_hours_array =
    typeof Listing_Calendar !== "undefined" &&
    Listing_Calendar.booked_hours_array
      ? Listing_Calendar.booked_hours_array
      : "";
  var pending_hours_array =
    typeof Listing_Calendar !== "undefined" &&
    Listing_Calendar.pending_hours_array
      ? Listing_Calendar.pending_hours_array
      : "";
  var completed_hours_array =
    typeof Listing_Calendar !== "undefined" &&
    Listing_Calendar.completed_hours_array
      ? Listing_Calendar.completed_hours_array
      : "";
  var booking_start_hour =
    typeof Listing_Calendar !== "undefined" &&
    Listing_Calendar.booking_start_hour
      ? Listing_Calendar.booking_start_hour
      : "01:00";
  var booking_end_hour =
    typeof Listing_Calendar !== "undefined" && Listing_Calendar.booking_end_hour
      ? Listing_Calendar.booking_end_hour
      : "24:00";
  var homey_is_rtl =
    typeof Listing_Calendar !== "undefined" && Listing_Calendar.homey_is_rtl
      ? Listing_Calendar.homey_is_rtl
      : "no";

  if (booked_hours_array !== "" && booked_hours_array.length !== 0) {
    booked_hours_array = JSON.parse(booked_hours_array);
  }

  if (completed_hours_array !== "" && completed_hours_array.length !== 0) {
    completed_hours_array = JSON.parse(completed_hours_array);
  }

  if (pending_hours_array !== "" && pending_hours_array.length !== 0) {
    pending_hours_array = JSON.parse(pending_hours_array);
  }

  var today = new Date();
  var listing_booked_dates = [];
  var listing_completed_dates = [];
  var listing_pending_dates = [];

  for (var key in booked_hours_array) {
    if (booked_hours_array.hasOwnProperty(key) && key !== "") {
      var temp_book = {};
      temp_book["title"] =
        typeof Listing_Calendar !== "undefined" &&
        Listing_Calendar.hc_reserved_label
          ? Listing_Calendar.hc_reserved_label
          : "Reserved";
      temp_book["start"] = moment.unix(key).utc().format();
      temp_book["end"] = moment.unix(booked_hours_array[key]).utc().format();
      temp_book["editable"] = false;
      temp_book["color"] = "#fdd2d2";
      temp_book["textColor"] = "#444444";
      temp_book["className"] = "fc-event-reserved";
      listing_booked_dates.push(temp_book);

      // Add prep time event
      const prepEvent = createPrepTimeEvent(temp_book);
      if (prepEvent) listing_booked_dates.push(prepEvent);
    }
  }

  // Do the same for pending and completed hours
  for (var key_pending in pending_hours_array) {
    if (pending_hours_array.hasOwnProperty(key_pending) && key_pending !== "") {
      var temp_pending = {};
      temp_pending["title"] =
        typeof Listing_Calendar !== "undefined" &&
        Listing_Calendar.hc_pending_label
          ? Listing_Calendar.hc_pending_label
          : "Pending";
      temp_pending["start"] = moment.unix(key_pending).utc().format();
      temp_pending["end"] = moment
        .unix(pending_hours_array[key_pending])
        .utc()
        .format();
      temp_pending["editable"] = false;
      temp_pending["color"] = "#ffeedb";
      temp_pending["textColor"] = "#333333";
      temp_pending["className"] = "fc-event-pending";
      listing_pending_dates.push(temp_pending);

      // Add prep time event
      const prepEvent = createPrepTimeEvent(temp_pending);
      if (prepEvent) listing_pending_dates.push(prepEvent);
    }
  }

  for (var key_booked in completed_hours_array) {
    if (completed_hours_array.hasOwnProperty(key_booked) && key_booked !== "") {
      var temp_complete = {};
      temp_complete["title"] =
        typeof Listing_Calendar !== "undefined" &&
        Listing_Calendar.hc_completed_label
          ? Listing_Calendar.hc_completed_label
          : "Completed";
      temp_complete["start"] = moment.unix(key_booked).utc().format();
      temp_complete["end"] = moment
        .unix(completed_hours_array[key_booked])
        .utc()
        .format();
      temp_complete["editable"] = false;
      temp_complete["color"] = "#fdd2d2";
      temp_complete["textColor"] = "#444444";
      temp_complete["className"] = "fc-event-completed";
      listing_completed_dates.push(temp_complete);

      // Add prep time event
      const prepEvent = createPrepTimeEvent(temp_complete);
      if (prepEvent) listing_completed_dates.push(prepEvent);
    }
  }

  var hours_slot = listing_booked_dates.concat(listing_pending_dates);
  hours_slot = hours_slot.concat(listing_completed_dates);

  // Function to safely parse JSON and handle malformed data
  function safeJsonParse(jsonString) {
    try {
      // First clean the string
      const cleanString = jsonString
        .replace(/&quot;/g, '"')
        .replace(/^['"]+|['"]+$/g, "");

      return cleanString ? JSON.parse(cleanString) : [];
    } catch (e) {
      console.error("JSON parse error:", e);
      return [];
    }
  }

  // Function to create a new instance with proper time slots handling
  function createNewInstance(instanceData = {}) {
    instanceCount++;
    selectedDates[instanceCount] = instanceData.selected_dates
      ? instanceData.selected_dates.split(",")
      : [];

    // Initialize time slots as array
    selectedTimeSlots[instanceCount] = [];

    // Handle time slots data - ensure it's always an array with proper service options
    if (instanceData.selected_time_slots) {
      let slotsData = [];

      // Parse the time slots data
      if (Array.isArray(instanceData.selected_time_slots)) {
        slotsData = instanceData.selected_time_slots;
      } else if (typeof instanceData.selected_time_slots === "string") {
        slotsData = safeJsonParse(instanceData.selected_time_slots);
      }

      // Initialize each slot with proper service options
      selectedTimeSlots[instanceCount] = slotsData.map((slot) => {
        return {
          time: slot.time,
          timeUnix: slot.timeUnix,
          dateStr: slot.dateStr,
          amenity: slot.amenity || instanceData.amenity || "available",
          sleeping: slot.sleeping || instanceData.sleeping || "available",
          gservice: slot.gservice || instanceData.gservice || "available",
        };
      });
    }

    activeInstanceId = instanceCount;

    // Get events for the selected dates
    const eventsForInstance = getEventsForSelectedDates(
      selectedDates[instanceCount],
      hours_slot
    );

    // Format events using the specified timezone
    const timezone =
      typeof Homey_Listing !== "undefined" && Homey_Listing.homey_timezone
        ? Homey_Listing.homey_timezone
        : "UTC";

    const formattedEvents = eventsForInstance.map((event) => {
      const startTime = moment(event.start)
        .tz(timezone)
        .format("MMM D, YYYY h:mm A");
      const endTime = moment(event.end)
        .tz(timezone)
        .format("MMM D, YYYY h:mm A");
      return `
        <div class="event-item">
          <strong>${event.title}</strong><br>
          ${startTime} - ${endTime}
        </div>
      `;
    });

    // Format time slots if they exist
    let timeSlotsHtml = "";
    if (
      selectedTimeSlots[instanceCount] &&
      selectedTimeSlots[instanceCount].length > 0
    ) {
      // Group by date
      const slotsByDate = {};
      selectedTimeSlots[instanceCount].forEach((slot) => {
        if (!slotsByDate[slot.dateStr]) {
          slotsByDate[slot.dateStr] = [];
        }
        slotsByDate[slot.dateStr].push(slot);
      });

      timeSlotsHtml =
        '<div class="time-slots-container"><h4>Selected Time Slots:</h4>';

      // Display grouped by date
      Object.keys(slotsByDate).forEach((dateStr) => {
        const formattedDate = formatDate(dateStr);
        timeSlotsHtml += `<div class="date-time-slots"><strong>${formattedDate[0]} ${formattedDate[1]}</strong><br><div class="time-slots-row">`;

        // Display each time slot for this date
        slotsByDate[dateStr].forEach((slot) => {
          timeSlotsHtml += `<div class="time-slot">${slot.time}</div>`;
        });

        timeSlotsHtml += "</div></div>";
      });

      timeSlotsHtml += "</div>";
    } else {
      timeSlotsHtml = "No time slots selected";
    }

    var showBookings = currentPriceType !== "price_per_hour";

    var instanceHtml = `
      <div class="instance" data-instance="${instanceCount}">
        <div class="dates-section">
          <h3>Selected ${
            currentPriceType === "price_per_hour" ? "Time Slots" : "Dates"
          }</h3>
          ${
            currentPriceType === "price_per_hour"
              ? `<div id="selected-time-slots-${instanceCount}">${
                  timeSlotsHtml || "No time slots selected"
                }</div>`
              : `<div id="selected-dates-${instanceCount}" style="display: flex; gap: 10px; flex-wrap: wrap;">${
                  selectedDates[instanceCount].length > 0
                    ? ""
                    : "No date selected"
                }</div>`
          }
          <input type="hidden" class="selected_dates" name="instances[${instanceCount}][selected_dates]" value="${
      instanceData.selected_dates || ""
    }">
          <div id="clear-dates-container-${instanceCount}" style="margin-top: 10px; margin-bottom: 10px;">
            <a href="javascript:void(0)" id="clear-dates-link-${instanceCount}" class="clear-dates">
              <span class="clear-dates-icon">x</span> <span class="clear-dates-text">Clear ${
                currentPriceType === "price_per_hour" ? "Time Slots" : "Dates"
              }</span>
            </a>
          </div>
          <h3>Availability</h3>
      <div class="row">
                <div class="col-lg-4 col-xs-12">
                    <label for="amenity-availability-${instanceCount}">Amenity Services</label>
                    <button type="button" class="btn btn-outline change-amenity">Change</button>
                    <div class="amenity-availability-options" style="display: none;">
                        <div class="cal-options"><input type="radio" name="instances[${instanceCount}][amenity]" value="available" ${
      instanceData.amenity === "available" || !instanceData.amenity
        ? "checked"
        : ""
    }> Available</div>
                        <div class="cal-options"><input type="radio" name="instances[${instanceCount}][amenity]" value="blocked" ${
      instanceData.amenity === "blocked" ? "checked" : ""
    }> Blocked</div>
                    </div>
                </div>
                <div class="col-lg-4 col-xs-12">
                    <label for="sleeping-availability-${instanceCount}">Sleeping Accommodation</label>
                    <button type="button" class="btn btn-outline change-sleeping">Change</button>
                    <div class="sleeping-availability-options" style="display: none;">
                        <div class="cal-options"><input type="radio" name="instances[${instanceCount}][sleeping]" value="available" ${
      instanceData.sleeping === "available" || !instanceData.sleeping
        ? "checked"
        : ""
    }> Available</div>
                        <div class="cal-options"><input type="radio" name="instances[${instanceCount}][sleeping]" value="blocked" ${
      instanceData.sleeping === "blocked" ? "checked" : ""
    }> Blocked</div>
                    </div>
                </div>
                <div class="col-lg-4 col-xs-12">
                    <label for="gservice-availability-${instanceCount}">Guided Services</label>
                    <button type="button" class="btn btn-outline change-gservice">Change</button>
                    <div class="gservice-availability-options" style="display: none;">
                        <div class="cal-options"><input type="radio" name="instances[${instanceCount}][gservice]" value="available" ${
      instanceData.gservice === "available" || !instanceData.gservice
        ? "checked"
        : ""
    }> Available</div>
                        <div class="cal-options"><input type="radio" name="instances[${instanceCount}][gservice]" value="blocked" ${
      instanceData.gservice === "blocked" ? "checked" : ""
    }> Blocked</div>
                    </div>
                </div>
            </div>
          ${
            showBookings
              ? `
            <h3 style="margin-top: 20px;">My Bookings</h3>
            <div id="events-for-instance-${instanceCount}" class="events-list">
              ${
                formattedEvents.length > 0
                  ? formattedEvents.join("")
                  : "No events for selected dates."
              }
            </div>
            `
              : ""
          }
        </div>
        <button type="button" class="btn btn-danger delete-instance">Delete</button>
      </div>
    `;

    document
      .getElementById("repeater-container")
      .insertAdjacentHTML("beforeend", instanceHtml);

    // Create the hidden input for time slots WITHOUT HTML entity encoding
    const timeSlotsInput = document.createElement("input");
    timeSlotsInput.type = "hidden";
    timeSlotsInput.className = "selected_time_slots";
    timeSlotsInput.name = `instances[${instanceCount}][selected_time_slots]`;

    // Store the raw JSON string in a data attribute
    const timeSlotsJson = JSON.stringify(selectedTimeSlots[instanceCount]);
    timeSlotsInput.setAttribute("data-raw-value", timeSlotsJson);
    timeSlotsInput.value = timeSlotsJson;

    // Add the input to your form
    document
      .querySelector(
        `.instance[data-instance="${instanceCount}"] .dates-section`
      )
      .appendChild(timeSlotsInput);

    // Set the active instance on click
    document.querySelectorAll(".instance").forEach(function (instance) {
      instance.addEventListener("click", function () {
        document.querySelectorAll(".instance").forEach(function (inst) {
          inst.classList.remove("active-instance");
        });

        this.classList.add("active-instance");
        activeInstanceId = this.getAttribute("data-instance");
      });
    });

    if (currentPriceType === "price_per_hour") {
      updateSelectedTimeSlots(instanceCount, selectedTimeSlots[instanceCount]);
    } else {
      updateSelectedDates(instanceCount, selectedDates[instanceCount]);
    }

    toggleClearDatesLink(
      instanceCount,
      currentPriceType === "price_per_hour"
        ? selectedTimeSlots[instanceCount].length > 0
        : selectedDates[instanceCount].length > 0
    );

    // Add saved dates to the calendar
    if (instanceData.selected_dates && currentPriceType !== "price_per_hour") {
      instanceData.selected_dates.split(",").forEach(function (dateStr) {
        calendar.addEvent({
          start: dateStr,
          display: "background",
          backgroundColor: "#f0ad4e",
        });
      });
    }

    // Add active class to the last instance
    const instances = document.querySelectorAll(".instance");
    if (instances.length > 0) {
      instances.forEach((inst) => inst.classList.remove("active-instance"));
      instances[instances.length - 1].classList.add("active-instance");
      activeInstanceId =
        instances[instances.length - 1].getAttribute("data-instance");
    }
  }

  function updateAllInstancesUI() {
    const showBookings = currentPriceType !== "price_per_hour";
    document.querySelectorAll(".instance").forEach((instance) => {
      const instanceId = instance.getAttribute("data-instance");
      const eventsSection = instance.querySelector(
        `#events-for-instance-${instanceId}`
      );

      if (eventsSection) {
        eventsSection.style.display = showBookings ? "block" : "none";
        const heading = eventsSection.previousElementSibling;
        if (
          heading &&
          heading.tagName === "H3" &&
          heading.textContent.includes("My Bookings")
        ) {
          heading.style.display = showBookings ? "block" : "none";
        }
      }

      if (currentPriceType === "price_per_hour") {
        // Switch to time slots display
        const timeSlotsHtml = `
                <div id="selected-time-slots-${instanceId}">
                    ${
                      selectedTimeSlots[instanceId] &&
                      selectedTimeSlots[instanceId].length > 0
                        ? ""
                        : "No time slots selected"
                    }
                </div>
            `;

        instance.querySelector(".dates-section h3:nth-of-type(1)").textContent =
          "Selected Time Slots";
        instance.querySelector(".clear-dates-text").textContent =
          "Clear Time Slots";

        const datesContainer = instance.querySelector(
          `#selected-dates-${instanceId}`
        );
        if (datesContainer) {
          datesContainer.outerHTML = timeSlotsHtml;
        }

        // Update the time slots display
        if (selectedTimeSlots[instanceId]) {
          updateSelectedTimeSlots(instanceId, selectedTimeSlots[instanceId]);
        }
      } else {
        // Switch to dates display
        const datesHtml = `
                <div id="selected-dates-${instanceId}" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    ${
                      selectedDates[instanceId] &&
                      selectedDates[instanceId].length > 0
                        ? ""
                        : "No date selected"
                    }
                </div>
            `;

        instance.querySelector(".dates-section h3:nth-of-type(1)").textContent =
          "Selected Dates";
        instance.querySelector(".clear-dates-text").textContent = "Clear Dates";

        const timeSlotsContainer = instance.querySelector(
          `#selected-time-slots-${instanceId}`
        );
        if (timeSlotsContainer) {
          timeSlotsContainer.outerHTML = datesHtml;
        }

        // Update the dates display
        if (selectedDates[instanceId]) {
          updateSelectedDates(instanceId, selectedDates[instanceId]);
        }
      }
    });
  }

  function initializeCalendar() {
    if (calendar) {
      calendar.destroy();
    }

    // Always start with month view regardless of price type
    const initialView = "dayGridMonth"; // Changed from conditional to always month view

    calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
      selectable: true,
      locale:
        typeof Homey_Listing !== "undefined" && Homey_Listing.homey_current_lang
          ? Homey_Listing.homey_current_lang
          : "en",
      timeZone:
        typeof Homey_Listing !== "undefined" && Homey_Listing.homey_timezone
          ? Homey_Listing.homey_timezone
          : "UTC",
      initialView: initialView,
      slotDuration: "00:30:00",
      minTime: booking_start_hour,
      maxTime: booking_end_hour,
      scrollTime: "00:00",
      scrollTimeReset: false,
      events: hours_slot,
      initialDate: today,
      dateClick: function (info) {
        if (currentPriceType === "price_per_hour") {
          calendar.changeView("timeGridDay", info.date);
        }
      },
      select: function (info) {
        // This will only trigger in day view where selection is enabled
        if (activeInstanceId === null) {
          alert(
            `Please select an instance before choosing ${
              currentPriceType === "price_per_hour" ? "time slots" : "dates"
            }.`
          );
          return;
        }

        if (currentPriceType === "price_per_hour") {
          handleTimeSlotSelection(info);
        } else {
          handleDateSelection(info);
        }
      },
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right:
          currentPriceType === "price_per_hour"
            ? "dayGridMonth,timeGridDay"
            : "dayGridMonth",
      },
      selectAllow: function (selectInfo) {
        // Only allow selection in day view for price_per_hour
        if (
          currentPriceType === "price_per_hour" &&
          calendar.view.type !== "timeGridDay"
        ) {
          return false;
        }

        // Get all events that overlap with the selection
        const overlappingEvents = calendar.getEvents().filter((event) => {
          return event.start < selectInfo.end && event.end > selectInfo.start;
        });

        // Check if any overlapping events are non-selectable types
        const hasBlockedEvents = overlappingEvents.some((event) => {
          return (
            event.classNames.includes("fc-event-reserved") ||
            event.classNames.includes("fc-event-pending") ||
            event.classNames.includes("fc-event-completed") ||
            event.classNames.includes("fc-event-prep")
          );
        });

        return !hasBlockedEvents;
      },
    });

    // Add booked/pending/completed events
    // hours_slot.forEach((event) => {
    //   calendar.addEvent(event);
    // });

    // Highlight selected dates
    Object.keys(selectedDates).forEach((instanceId) => {
      selectedDates[instanceId].forEach((dateStr) => {
        calendar.addEvent({
          start: dateStr,
          display: "background",
          backgroundColor: "#f0ad4e",
          className: "fc-bg-event fc-event fc-event-start fc-event-end",
        });
      });
    });

    // Highlight selected time slots
    highlightSelectedTimeSlotsOnCalendar();

    calendar.render();
  }

  function highlightSelectedTimeSlotsOnCalendar() {
    // First remove all existing highlights
    calendar.getEvents().forEach((event) => {
      if (event.backgroundColor === "#f0ad4e") {
        event.remove();
      }
    });

    // Highlight all selected time slots across all instances
    Object.entries(selectedTimeSlots).forEach(([instanceId, slots]) => {
      if (Array.isArray(slots)) {
        slots.forEach((slot) => {
          const start = moment.unix(slot.timeUnix).toDate();
          const end = moment.unix(slot.timeUnix).add(30, "minutes").toDate();

          // Use the slot's own service options - these should now be properly initialized
          const amenityStatus = slot.amenity || "available";
          const sleepingStatus = slot.sleeping || "available";
          const gserviceStatus = slot.gservice || "available";

          let title = `\nAmenity: ${amenityStatus}, `;
          title += `\nSleeping Accomodation: ${sleepingStatus}, `;
          title += `\nGuided Service: ${gserviceStatus}`;

          calendar.addEvent({
            title: title,
            start: start,
            end: end,
            display: "background",
            backgroundColor: "#f0ad4e",
            className: "fc-bg-event fc-event fc-event-start fc-event-end",
            extendedProps: {
              amenity: amenityStatus,
              sleeping: sleepingStatus,
              gservice: gserviceStatus,
            },
          });
        });
      }
    });
  }

  // Add this after your existing event listeners
  document.addEventListener("change", function (event) {
    if (currentPriceType !== "price_per_hour") return;
    if (
      event.target.matches(
        '.amenity-availability-options input[type="radio"], ' +
          '.sleeping-availability-options input[type="radio"], ' +
          '.gservice-availability-options input[type="radio"]'
      )
    ) {
      if (activeInstanceId) {
        // Update all time slots for this instance with the new values
        const instanceElement = document.querySelector(
          `.instance[data-instance="${activeInstanceId}"]`
        );

        const amenityStatus =
          instanceElement.querySelector(
            `input[name="instances[${activeInstanceId}][amenity]"]:checked`
          )?.value || "available";
        const sleepingStatus =
          instanceElement.querySelector(
            `input[name="instances[${activeInstanceId}][sleeping]"]:checked`
          )?.value || "available";
        const gserviceStatus =
          instanceElement.querySelector(
            `input[name="instances[${activeInstanceId}][gservice]"]:checked`
          )?.value || "available";

        // Update the selectedTimeSlots for this instance
        if (selectedTimeSlots[activeInstanceId]) {
          selectedTimeSlots[activeInstanceId].forEach((slot) => {
            slot.amenity = amenityStatus;
            slot.sleeping = sleepingStatus;
            slot.gservice = gserviceStatus;
          });

          // Update the hidden input
          const input = document.querySelector(
            `input[name="instances[${activeInstanceId}][selected_time_slots]"]`
          );
          const jsonValue = JSON.stringify(selectedTimeSlots[activeInstanceId]);
          input.value = jsonValue;
          input.setAttribute("data-raw-value", jsonValue);
        }

        // Refresh the calendar display
        highlightSelectedTimeSlotsOnCalendar();
      }
    }
  });

  // Function to check if time slot is already selected in any instance
  function isTimeSlotSelected(timeUnix, dateStr, currentInstanceId) {
    for (const instanceId in selectedTimeSlots) {
      if (instanceId !== currentInstanceId.toString()) {
        const exists = selectedTimeSlots[instanceId].some(
          (slot) => slot.timeUnix === timeUnix && slot.dateStr === dateStr
        );
        if (exists) return true;
      }
    }
    return false;
  }

  function handleTimeSlotSelection(info) {
    if (activeInstanceId === null) {
      alert("Please select an instance before choosing time slots.");
      return;
    }

    const timezone = Homey_Listing?.homey_timezone || "UTC";
    const startMoment = moment(info.start).tz(timezone);
    const endMoment = moment(info.end).tz(timezone);

    if (!selectedTimeSlots[activeInstanceId]) {
      selectedTimeSlots[activeInstanceId] = [];
    }

    const instanceElement = document.querySelector(
      `.instance[data-instance="${activeInstanceId}"]`
    );
    const amenityStatus =
      instanceElement.querySelector(
        'input[name="instances[' + activeInstanceId + '][amenity]"]:checked'
      )?.value || "available";
    const sleepingStatus =
      instanceElement.querySelector(
        'input[name="instances[' + activeInstanceId + '][sleeping]"]:checked'
      )?.value || "available";
    const gserviceStatus =
      instanceElement.querySelector(
        'input[name="instances[' + activeInstanceId + '][gservice]"]:checked'
      )?.value || "available";

    const slotDuration = 30; // minutes
    let currentTime = startMoment.clone();
    let hasConflict = false;

    // First check for conflicts
    while (currentTime < endMoment) {
      const dateStr = currentTime.format("YYYY-MM-DD");
      const timeUnix = currentTime.unix();

      if (isTimeSlotSelected(timeUnix, dateStr, activeInstanceId)) {
        hasConflict = true;
        break;
      }
      currentTime.add(slotDuration, "minutes");
    }

    if (hasConflict) {
      alert(
        "Some of these time slots are already selected in another instance. Please choose different time slots."
      );
      return;
    }

    // Reset and actually add the slots
    currentTime = startMoment.clone();
    while (currentTime < endMoment) {
      const slotEnd = currentTime.clone().add(slotDuration, "minutes");
      const dateStr = currentTime.format("YYYY-MM-DD");
      const timeStr = currentTime.format("h:mm A");
      const timeUnix = currentTime.unix();

      const timeSlot = {
        time: timeStr,
        timeUnix: timeUnix,
        dateStr: dateStr,
        amenity: amenityStatus,
        sleeping: sleepingStatus,
        gservice: gserviceStatus,
      };

      // Check if already selected in current instance
      const exists = selectedTimeSlots[activeInstanceId].some(
        (slot) => slot.timeUnix === timeUnix && slot.dateStr === dateStr
      );

      if (!exists) {
        selectedTimeSlots[activeInstanceId].push(timeSlot);

        // Add visual indicator
        const backgroundColor = "#f0ad4e";

        // Add service status to title
        let title = `\nAmenity: ${amenityStatus}, `;
        title += `\nSleeping Accomodation: ${sleepingStatus}, `;
        title += `\nGuided Service: ${gserviceStatus}`;

        calendar.addEvent({
          title: title,
          start: currentTime.toDate(),
          end: slotEnd.toDate(),
          display: "background",
          backgroundColor: backgroundColor,
          className: "fc-bg-event fc-event fc-event-start fc-event-end",
          extendedProps: {
            amenity: amenityStatus,
            sleeping: sleepingStatus,
            gservice: gserviceStatus,
          },
        });
      }

      currentTime.add(slotDuration, "minutes");
    }

    // Sort all slots by date and time
    selectedTimeSlots[activeInstanceId].sort((a, b) => {
      if (a.dateStr === b.dateStr) return a.timeUnix - b.timeUnix;
      return new Date(a.dateStr) - new Date(b.dateStr);
    });

    // Update UI
    updateSelectedTimeSlots(
      activeInstanceId,
      selectedTimeSlots[activeInstanceId]
    );
    toggleClearDatesLink(activeInstanceId, true);

    // Update hidden field
    const input = document.querySelector(
      `input[name="instances[${activeInstanceId}][selected_time_slots]"]`
    );
    const jsonValue = JSON.stringify(selectedTimeSlots[activeInstanceId]);
    input.value = jsonValue;
    input.setAttribute("data-raw-value", jsonValue);
  }

  function updateSelectedTimeSlots(instanceId, timeSlotsArray) {
    let timeSlotsHtml = "";

    if (timeSlotsArray && timeSlotsArray.length > 0) {
      // Group by date
      const groupedByDate = {};
      timeSlotsArray.forEach((slot) => {
        if (!groupedByDate[slot.dateStr]) {
          groupedByDate[slot.dateStr] = [];
        }
        groupedByDate[slot.dateStr].push(slot);
      });

      timeSlotsHtml = '<div class="time-slots-container">';

      // Display each date group
      for (const dateStr in groupedByDate) {
        const formattedDate = formatDate(dateStr);
        timeSlotsHtml += `<div class="date-time-slots"><strong>${formattedDate[0]} ${formattedDate[1]}</strong><br><div class="time-slots-row">`;

        // Display each time slot for this date
        groupedByDate[dateStr].forEach((slot) => {
          timeSlotsHtml += `<div class="time-slot">${slot.time}</div>`;
        });

        timeSlotsHtml += "</div></div>";
      }
      timeSlotsHtml += "</div>";
    } else {
      timeSlotsHtml = "No time slots selected";
    }

    document.querySelector(`#selected-time-slots-${instanceId}`).innerHTML =
      timeSlotsHtml;
  }

  function handleDateSelection(info) {
    var startDate = info.startStr;
    var endDate = info.endStr;

    // Check if an active instance is selected
    if (
      activeInstanceId !== null &&
      selectedDates[activeInstanceId] !== undefined
    ) {
      // Get all dates in the range (excluding the end date)
      var allDates = getAllDatesInRange(new Date(startDate), new Date(endDate));

      // Check if any of the dates overlap with existing selections in other instances
      if (isDateRangeAvailable(allDates, activeInstanceId)) {
        // Add each date to the selectedDates array or remove if already selected
        allDates.forEach(function (date) {
          var dateStr = date.toISOString().split("T")[0];
          var dateIndex = selectedDates[activeInstanceId].indexOf(dateStr);
          if (dateIndex === -1) {
            // Date is not selected, add it
            selectedDates[activeInstanceId].push(dateStr);
            calendar.addEvent({
              start: dateStr,
              display: "background",
              backgroundColor: "#f0ad4e",
            });
          } else {
            // Date is already selected, remove it
            selectedDates[activeInstanceId].splice(dateIndex, 1);
            var event = calendar.getEvents().find(function (ev) {
              return ev.startStr === dateStr;
            });
            if (event) {
              event.remove();
            }
          }
        });

        // Update the UI and hidden input field
        updateSelectedDates(activeInstanceId, selectedDates[activeInstanceId]);
        toggleClearDatesLink(
          activeInstanceId,
          selectedDates[activeInstanceId].length > 0
        );
        updateHiddenFields(activeInstanceId, selectedDates[activeInstanceId]);

        // Update the event list
        updateEventList(
          activeInstanceId,
          selectedDates[activeInstanceId],
          hours_slot
        );
      } else {
        alert(
          "This date range overlaps with an existing selection in another instance."
        );
      }
    } else {
      alert("Please select an instance before choosing dates.");
    }
  }

  // Check if the date range is available across all instances
  function isDateRangeAvailable(dates, currentInstanceId) {
    for (var instanceId in selectedDates) {
      if (instanceId !== currentInstanceId.toString()) {
        for (var i = 0; i < selectedDates[instanceId].length; i++) {
          var selectedDate = selectedDates[instanceId][i];
          if (dates.includes(selectedDate)) {
            return false; // Overlapping date found
          }
        }
      }
    }
    return true; // No overlapping dates found
  }

  // Event listener for clearing dates and deleting instances
  document.addEventListener("click", function (event) {
    // Clear dates when clicking on the clear-dates container
    if (event.target.closest(".clear-dates")) {
      var instanceId = event.target
        .closest(".instance")
        .getAttribute("data-instance");

      if (currentPriceType === "price_per_hour") {
        // Remove time slot events for this instance
        calendar.getEvents().forEach(function (event) {
          if (event.backgroundColor === "#f0ad4e") {
            event.remove();
          }
        });

        // Clear selected time slots for this instance
        selectedTimeSlots[instanceId] = [];
        updateSelectedTimeSlots(instanceId, selectedTimeSlots[instanceId]);
        toggleClearDatesLink(instanceId, false);

        // Update hidden field
        document.querySelector(
          `input[name="instances[${instanceId}][selected_time_slots]"]`
        ).value = JSON.stringify([]);

        // Re-highlight remaining time slots from other instances
        highlightSelectedTimeSlotsOnCalendar();
      } else {
        // Original date clearing logic
        calendar.getEvents().forEach(function (event) {
          if (selectedDates[instanceId].includes(event.startStr)) {
            event.remove();
          }
        });

        selectedDates[instanceId] = [];
        updateSelectedDates(instanceId, selectedDates[instanceId]);
        toggleClearDatesLink(instanceId, false);
        updateHiddenFields(instanceId, selectedDates[instanceId]);
        updateEventList(instanceId, selectedDates[instanceId], hours_slot);
      }
    }

    // Delete instance
    if (event.target.classList.contains("delete-instance")) {
      var instanceId = event.target
        .closest(".instance")
        .getAttribute("data-instance");

      // Remove events for this specific instance
      calendar.getEvents().forEach(function (event) {
        if (
          selectedDates[instanceId] &&
          selectedDates[instanceId].includes(event.startStr)
        ) {
          event.remove();
        }
        if (event.backgroundColor === "#f0ad4e") {
          event.remove();
        }
      });

      // Clear selected dates/time slots and delete the instance
      delete selectedDates[instanceId];
      delete selectedTimeSlots[instanceId];
      event.target.closest(".instance").remove();

      // Reset activeInstanceId if it points to the deleted instance
      if (activeInstanceId === instanceId) {
        activeInstanceId = null;
      }
    }

    // Toggle availability options
    if (
      event.target.classList.contains("change-amenity") ||
      event.target.classList.contains("change-sleeping") ||
      event.target.classList.contains("change-gservice")
    ) {
      var optionsDiv = event.target.nextElementSibling;
      if (optionsDiv.style.display === "none") {
        optionsDiv.style.display = "block";
      } else {
        optionsDiv.style.display = "none";
      }
    }
  });

  // Functions for updating UI and hidden fields
  function updateSelectedDates(instanceId, selectedDates) {
    var selectedDatesHtml = "";

    // Sort the dates
    selectedDates.sort(function (a, b) {
      return new Date(a) - new Date(b);
    });

    // Display each date
    selectedDates.forEach(function (dateStr) {
      var formattedDate = formatDate(dateStr);
      selectedDatesHtml += `
                  <div class='date-container'>
                      <div class='month'>${formattedDate[0]}</div>
                      <div class='day'>${formattedDate[1]}</div>
                  </div>
              `;
    });

    if (selectedDatesHtml === "") {
      selectedDatesHtml = "No date selected";
    }

    document.querySelector(`#selected-dates-${instanceId}`).innerHTML =
      selectedDatesHtml;

    toggleClearDatesLink(instanceId, selectedDates.length > 0);
  }

  function toggleClearDatesLink(instanceId, hasSelection) {
    var clearDatesLink = document.querySelector(
      `#clear-dates-link-${instanceId}`
    );
    if (clearDatesLink) {
      if (hasSelection) {
        clearDatesLink.style.display = "inline-block";
      } else {
        clearDatesLink.style.display = "none";
      }
    }
  }

  function updateHiddenFields(instanceId, selectedDates) {
    // Update the hidden input field with all selected dates
    document.querySelector(
      `input[name="instances[${instanceId}][selected_dates]"]`
    ).value = selectedDates.join(",");
  }

  function formatDate(dateStr) {
    var date = new Date(dateStr);
    var options = {
      month: "short",
      day: "numeric",
    };
    return date.toLocaleDateString("en-US", options).split(" ");
  }

  function getAllDatesInRange(startDate, endDate) {
    var date = new Date(startDate);
    var dates = [];

    while (date < endDate) {
      dates.push(new Date(date));
      date.setDate(date.getDate() + 1);
    }
    return dates;
  }

  function getEventsForSelectedDates(selectedDates, hours_slot) {
    return hours_slot.filter(function (event) {
      return selectedDates.some(function (dateStr) {
        return event.start.includes(dateStr);
      });
    });
  }

  function updateEventList(instanceId, selectedDates, hours_slot) {
    const timezone =
      typeof Homey_Listing !== "undefined" && Homey_Listing.homey_timezone
        ? Homey_Listing.homey_timezone
        : "UTC";

    // Filter events for the selected dates
    const eventsForInstance = getEventsForSelectedDates(
      selectedDates,
      hours_slot
    );

    // Format events using the specified timezone
    const formattedEvents = eventsForInstance.map((event) => {
      const startTime = moment(event.start)
        .tz(timezone)
        .format("MMM D, YYYY h:mm A");
      const endTime = moment(event.end)
        .tz(timezone)
        .format("MMM D, YYYY h:mm A");
      return `
        <div class="event-item">
          <strong>${event.title}</strong><br>
          ${startTime} - ${endTime}
        </div>
      `;
    });

    // Update the event list in the DOM
    const eventListContainer = document.querySelector(
      `#events-for-instance-${instanceId}`
    );
    if (eventListContainer) {
      eventListContainer.innerHTML =
        formattedEvents.length > 0
          ? formattedEvents.join("")
          : "No events for selected dates.";
    }
  }

  // Fix existing malformed time slots inputs on page load
  function fixExistingTimeSlotInputs() {
    document.querySelectorAll(".selected_time_slots").forEach((input) => {
      try {
        const rawValue =
          input.getAttribute("data-raw-value") ||
          input.value.replace(/&quot;/g, '"');

        if (rawValue) {
          const parsed = JSON.parse(rawValue);
          const cleanJson = JSON.stringify(parsed);
          input.value = cleanJson;
          input.setAttribute("data-raw-value", cleanJson);
        }
      } catch (e) {
        console.error("Error fixing time slots input:", e);
        input.value = "[]";
        input.setAttribute("data-raw-value", "[]");
      }
    });
  }

  var addNewInstance = document.getElementById("add-new-instance");
  if (addNewInstance) {
    addNewInstance.addEventListener("click", function () {
      createNewInstance();
    });
  }

  initializeCalendar();

  var calendarTabLink = document.getElementById("listing-calendar-tab-link");
  if (calendarTabLink) {
    calendarTabLink.addEventListener("click", function (e) {
      setTimeout(function () {
        initializeCalendar();
      }, 200);
    });
  }

  // Fix existing inputs on page load
  fixExistingTimeSlotInputs();

  // Load existing instances or create a new one
  if (
    typeof Listing_Calendar.instances === "object" &&
    Listing_Calendar.instances !== null &&
    Object.keys(Listing_Calendar.instances).length > 0
  ) {
    Object.values(Listing_Calendar.instances).forEach(function (instanceData) {
      // Ensure selected_time_slots is properly formatted
      if (typeof instanceData.selected_time_slots === "string") {
        instanceData.selected_time_slots = safeJsonParse(
          instanceData.selected_time_slots
        );
      }
      createNewInstance(instanceData);
    });
  }
  // else {
  //   createNewInstance();
  // }

  setTimeout(() => {
    highlightSelectedTimeSlotsOnCalendar();
  }, 500);
});
