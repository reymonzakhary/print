export const useBasketAnimation = () => {
  /**
   * Creates and animates a flying element from source to target
   * @param {Object} sourceElement - Source DOM element reference
   * @param {Object} targetElement - Target DOM element reference
   * @param {Object} itemData - Item data to display in animation
   * @returns {Promise} Resolves when animation completes
   */
  const animateAddToBasket = (sourceElement, targetElement, itemData) => {
    if (
      !sourceElement ||
      !targetElement ||
      typeof sourceElement.getBoundingClientRect !== "function"
    ) {
      console.warn("Animation elements not available. Skipping animation.");
      return Promise.resolve();
    }

    const sourceRect = sourceElement.getBoundingClientRect();
    const targetRect = targetElement.getBoundingClientRect();

    if (sourceRect.width === 0 || sourceRect.height === 0) {
      console.warn("Source element has zero dimensions. Skipping animation.");
      return Promise.resolve();
    }

    const flyer = createFlyerElement(itemData, sourceRect);

    // Calculate target position
    const targetFlyerWidth = 150;
    const targetX = targetRect.left + targetRect.width * 0.1;
    const targetY = targetRect.top + targetRect.height * 0.1;

    return new Promise((resolve) => {
      // Force reflow before animation starts
      requestAnimationFrame(() => {
        // Start animation
        flyer.style.left = `${targetX}px`;
        flyer.style.top = `${targetY}px`;
        flyer.style.transform = "scale(1)";
        flyer.style.opacity = "1";
        flyer.style.width = `${targetFlyerWidth}px`;

        let transitionEnded = false;
        const onTransitionEnd = (event) => {
          if (
            event.target !== flyer ||
            !["opacity", "transform"].some((prop) => event.propertyName.includes(prop))
          ) {
            return;
          }

          // Fade out after reaching destination
          if (parseFloat(flyer.style.opacity) === 1 && !transitionEnded) {
            flyer.style.opacity = "0";
            return;
          }

          // Cleanup after animation completes
          if (!transitionEnded) {
            transitionEnded = true;
            flyer.removeEventListener("transitionend", onTransitionEnd);
            document.body.contains(flyer) && document.body.removeChild(flyer);
            resolve();
          }
        };

        flyer.addEventListener("transitionend", onTransitionEnd);

        // Safety timeout
        setTimeout(() => {
          if (!transitionEnded) {
            console.warn("Animation timeout triggered.");
            flyer.removeEventListener("transitionend", onTransitionEnd);
            document.body.contains(flyer) && document.body.removeChild(flyer);
            resolve();
          }
        }, 1500);
      });
    });
  };

  /**
   * Creates the flying element DOM node
   */
  const createFlyerElement = (itemData, startRect) => {
    const flyer = document.createElement("div");
    flyer.style.position = "fixed";
    flyer.style.left = `${startRect.left}px`;
    flyer.style.top = `${startRect.top}px`;
    flyer.style.width = `${startRect.width}px`;
    flyer.style.height = "auto";
    flyer.style.zIndex = "9999";
    flyer.style.backgroundColor = "white";
    flyer.style.border = "1px solid #e0e0e0";
    flyer.style.borderRadius = "8px";
    flyer.style.padding = "8px";
    flyer.style.display = "flex";
    flyer.style.alignItems = "center";
    flyer.style.gap = "8px";
    flyer.style.opacity = "0";
    flyer.style.transform = "scale(0.1)";
    flyer.style.transformOrigin = "center center";
    flyer.style.transition = `
      transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94),
      opacity 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94),
      left 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94),
      top 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94),
      width 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94)
    `;
    flyer.style.boxShadow = "0 4px 12px rgba(0,0,0,0.15)";

    const img = document.createElement("img");
    img.src = itemData.image || "";
    img.alt = itemData.name || "Product";
    img.style.width = "40px";
    img.style.height = "40px";
    img.style.objectFit = "contain";
    img.style.borderRadius = "4px";
    flyer.appendChild(img);

    const detailsDiv = document.createElement("div");
    detailsDiv.style.display = "flex";
    detailsDiv.style.flexDirection = "column";
    detailsDiv.style.fontSize = "10px";
    detailsDiv.style.overflow = "hidden";

    const nameP = document.createElement("p");
    nameP.textContent = itemData.name || "Unknown Product";
    nameP.style.fontWeight = "bold";
    nameP.style.whiteSpace = "nowrap";
    nameP.style.overflow = "hidden";
    nameP.style.textOverflow = "ellipsis";
    nameP.style.marginBottom = "2px";
    detailsDiv.appendChild(nameP);

    const priceP = document.createElement("p");
    priceP.textContent = itemData.price || "N/A";
    detailsDiv.appendChild(priceP);

    flyer.appendChild(detailsDiv);
    document.body.appendChild(flyer);

    return flyer;
  };

  return {
    animateAddToBasket,
  };
};
