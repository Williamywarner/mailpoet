import React from 'react';
import classNames from 'classnames';
import { Link } from 'react-router';
import PropTypes from 'prop-types';
import MailPoet from 'mailpoet';

class Breadcrumb extends React.Component {
  constructor(props) {
    super(props);
    const steps = props.steps || [
      {
        name: 'type',
        label: MailPoet.I18n.t('selectType'),
        link: '/new',
      },
      {
        name: 'template',
        label: MailPoet.I18n.t('template'),
      },
      {
        name: 'editor',
        label: MailPoet.I18n.t('designer'),
      },
      {
        name: 'send',
        label: MailPoet.I18n.t('send'),
      },
    ];

    this.state = {
      step: null,
      steps,
    };
  }

  render() {
    const steps = this.state.steps.map((step, index) => {
      const stepClasses = classNames(
        { mailpoet_current: (this.props.step === step.name) }
      );

      let label = step.label;

      if (step.link !== undefined && this.props.step !== step.name) {
        label = (
          <Link to={step.link}>{ step.label }</Link>
        );
      }

      return (
        <span key={`step-${step.label}`}>
          <span className={stepClasses}>
            { label }
          </span>
          { (index < (this.state.steps.length - 1)) ? ' > ' : '' }
        </span>
      );
    });

    return (
      <p className="mailpoet_breadcrumb">
        { steps }
      </p>
    );
  }
}

Breadcrumb.propTypes = {
  steps: PropTypes.arrayOf(PropTypes.object),
  step: PropTypes.string,
};

Breadcrumb.defaultProps = {
  steps: undefined,
  step: null,
};

module.exports = Breadcrumb;
