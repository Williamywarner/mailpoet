import React from 'react';
import ReactDOMServer from 'react-dom/server';
import classNames from 'classnames';
import MailPoet from 'mailpoet';

const InAppAnnouncementDot = props => (
  <span
    role="button"
    tabIndex="-1"
    className={classNames('mailpoet_in_app_announcement_pulsing_dot', props.className)}
    onClick={() => {
      MailPoet.Modal.popup({
        template: ReactDOMServer.renderToString(props.children),
        width: props.width,
        height: props.height,
      });
      if (props.onUserTrigger) props.onUserTrigger();
    }}
  />
);

InAppAnnouncementDot.propTypes = {
  children: React.PropTypes.element.isRequired,
  width: React.PropTypes.string,
  height: React.PropTypes.string,
  className: React.PropTypes.string,
  onUserTrigger: React.PropTypes.func,
};

InAppAnnouncementDot.defaultProps = {
  width: 'auto',
  height: 'auto',
  className: null,
  onUserTrigger: null,
};

module.exports = InAppAnnouncementDot;
